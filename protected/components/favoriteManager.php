<?php

// client class accessed by controllers to start downloads
abstract class favoriteManager extends CModel {
  abstract function startDownload($opts, $status);

  private $toStart = array();
  private $toQueue = array();
  private $duplicates = array();

  protected function getTimeLimitSQL($timeLimit=true)
  {
    if($timeLimit === true)
    {
      $timeLimit = Yii::app()->dvrConfig->matchingTimeLimit;
      if($timeLimit == 0)
        return '';
      $timeLimit = $timeLimit*60*60;
    }

    $timeLimitSQL = '';
    if(is_integer($timeLimit))
      $timeLimitSQL = ' AND feedItem_pubDate>=(SELECT pubDate-'.$timeLimit.' FROM feedItem ORDER BY pubDate DESC LIMIT 1)';
    elseif(is_string($timeLimit))
      $timeLimitSQL = $timeLimit; 
    return $timeLimitSQL;
  }

  /**
   * Check a single favorite for matching
   * @param BaseFavorite $favorite 
   * @param integer $itemStatus
   */
  public function checkFavorite($favorite, $itemStatus = feedItem::STATUS_NOMATCH)
  {
    switch(get_class($favorite))
    {
      case 'favoriteTvShow':
        $this->checkTvShowFavorites($itemStatus, true, $favorite);
        break;
      case 'favoriteMovie':
        $this->checkMovieFavorites($itemStatus, true, $favorite);
        break;
      case 'favoriteString':
        $this->checkStringFavorites($itemStatus, true, $favorite);
        break;
      default:
        throw new CException("Argument must be a subclass of BaseFavorite. Given: ".get_class($favorite));
    }
    $this->startDownloads();

    $this->updateItemStatus($itemStatus);
  }

  // there must be a better way than the following for multiple favorites . . .
  /**
   * looks for feedItems that matching a favorite in the database
   * @param integer $itemStatus a feeditem status to limit the search to
   * @param mixed $timeLimit either boolean true/false for defaults, or integer timeLimit in secconds
   */
  public function checkFavorites($itemStatus = feedItem::STATUS_NEW, $timeLimit=true) 
  {
    Yii::trace('Checking for matching favorites');
    $this->checkTvShowFavorites($itemStatus, $timeLimit);
    $this->checkMovieFavorites($itemStatus, $timeLimit);
    $this->checkStringFavorites($itemStatus, $timeLimit);

    $this->startDownloads();

    $this->updateItemStatus($itemStatus);
  }
 
  /**
   * looks for feedItems that matching a favoriteMovie in the database
   * @param integer a feeditem status to limit the search to
   */
  protected function checkMovieFavorites($itemStatus = feedItem::STATUS_NEW, $timeLimit=true, $fav=null)
  {
    Yii::log('Looking for movie favorites');
    $db = Yii::app()->db;

    $limitFavorite =  ($fav ? ' AND favoriteMovies_id = '.$fav->id : '');
    $trans = $db->beginTransaction();
    try {
        // Mark any previously downloaded movies that are now matching
        $db->createCommand(
            'UPDATE feedItem'.
            '   SET status='.feedItem::STATUS_DUPLICATE.
            ' WHERE feedItem.status = '.$itemStatus.
            '   AND feedItem.id IN ( SELECT feedItem_id'.
            '    FROM matchingFavoriteMovies m'.
            '   WHERE m.movie_status = '.movie::STATUS_DOWNLOADED.
            $limitFavorite.
            ');'
            )->execute();
        $trans->commit();
    } catch (Exception $e) {
        $trans->rollback();
        throw $e;
    }

    // Get any matching items from the db
    $reader = $db->createCommand(
        'SELECT * FROM matchingFavoriteMovies'.
        ' WHERE feedItem_status='.$itemStatus.
        $limitFavorite.
        $this->getTimeLimitSQL($timeLimit).
        '   AND movie_status='.movie::STATUS_NEW
    )->queryAll();

    foreach($reader as $row) 
    {
      if($row['favorite_queue'])
        $this->toQueue[] = $row['feedItem_id'];
      else
        $this->toStart['movie.'.$row['movie_id']][] = $row;
    }
  }

  /**
   * looks for feedItems that matching a favoriteString in the database
   * and starts them.   Needs some work to prevent duplicate downloads
   * @param integer a feeditem status to limit the search to
   */
  protected function checkStringFavorites($itemStatus = feedItem::STATUS_NEW, $timeLimit=true, $fav=null)
  {
    Yii::log('Looking for string favorites');
    $db = Yii::app()->db;
    $reader = $db->createCommand(
        'SELECT * FROM matchingFavoriteStrings'.
        '  WHERE feedItem_status='.$itemStatus.
        ($fav ? ' AND favoriteStrings_id = '.$fav->id : '').
        $this->getTimeLimitSQL($timeLimit)
    )->queryAll();

    foreach($reader as $row) 
    {
      if($row['favorite_queue'] == 1)
        $this->toQueue[] = $row['feedItem_id'];
      else
        $this->toStart[][] = $row;
    }
  }

  protected function markOldEpisodes($itemStatus, $fav=null)
  {
    // Start by limiting to the tvShows with a favorite with only newer flagged
    $tvShowIdsSql =
        "SELECT tvShow_id FROM favoriteTvShows WHERE onlyNewer = 1".
        ($fav ? ' AND id = '.$fav->id : '');

    // Get the id of the newest tv episode of each tvShow in our set
    $newEpisodeIdsSql =
        "SELECT id FROM ".
        "( SELECT id,tvShow_id FROM tvEpisode ".
        "  WHERE tvShow_id IN ( $tvShowIdsSql )".
        "  ORDER BY season,episode".
        ")".
        "GROUP BY tvShow_id";

    // Get the id of every episode that is not the newest of each tvShow in our set
    // sqlite is smart enough to only make the $tvShowIdsSql query once even
    // if we use it twice
    $notNewEpisodeIdsSql =
        "SELECT id FROM tvEpisode".
        " WHERE tvShow_id IN ( $tvShowIdsSql )".
        "   AND id NOT IN ( $newEpisodeIdsSql )";

    // update all feed items of proper status that are in the notNew tvEpisode query
    Yii::app()->db->createCommand(
        "UPDATE feedItem".
        "   SET status = ".feedItem::STATUS_OLD.
        " WHERE tvEpisode_id NOT NULL".
        "   AND status = ".$itemStatus.
        "   AND tvEpisode_id IN ( $notNewEpisodeIdsSql )"
    )->execute();
  }

  /**
   * looks for feedItems that matching a favoriteTvShow in the database
   * @param integer $itemStatus a feeditem status to limit the search to
   */
  protected function checkTvShowFavorites($itemStatus = feedItem::STATUS_NEW, $timeLimit=true, $fav=null)
  {
    Yii::log('Looking for TvShow favorites');
    $db = Yii::app()->db;

    $trans = $db->beginTransaction();
    try {
        // Mark any duplicate episodes
        $db->createCommand(
            'UPDATE feedItem'.
            '   SET status='.feedItem::STATUS_DUPLICATE.
            ' WHERE feedItem.status = '.$itemStatus.
            '   AND feedItem.tvEpisode_id IN '.
            ' ( SELECT id FROM tvEpisode e'.
            '   WHERE e.status = '.tvEpisode::STATUS_DOWNLOADED.
            ' );'
            )->execute();

        $this->markOldEpisodes($itemStatus, $fav);
        $trans->commit();
    } catch (Exception $e) {
        $trans->rollback();
        throw $e;
    }


    // get any matching items with the right itemStatus
    $reader = $db->createCommand(
        'SELECT * FROM matchingFavoriteTvShows'.
        ' WHERE feedItem_status='.$itemStatus.
        $this->getTimeLimitSQL($timeLimit).
        ($fav ? ' AND favoriteTvShows_id = '.$fav->id : '').
        '   AND tvEpisode_status='.tvEpisode::STATUS_NEW
    )->queryAll();

    // Go through the resulting dataset and seperate into the queue and start
    // arrays
    foreach($reader as $row) 
    {
      if($row['favorite_queue'] == 1)
        $this->toQueue[] = $row['feedItem_id'];
      else // group the duplicates by tvEpisode_id
        $this->toStart['tvEpisode.'.$row['tvEpisode_id']][] = $row;
    }
  }

  /**
   * start downloading any items tagged in the toStart array
   * append un-started duplicates to the duplicates array
   */
  private function startDownloads() 
  {
    foreach($this->toStart as $items) 
    {
      // For now just take the first item of each but this
      // structure allows there to be a decision making process
      // to decide which item based on feed priority or something.
      $success = false;
      foreach($items as $item) 
      {
        if($success) 
          $this->duplicates[] = $item['feedItem_id'];
        else
          $success = $this->startDownload($item, feedItem::STATUS_AUTO_DL);
      }
    }
    $this->toStart = array();
  }

  /**
   * update the status of all feedItems tagged in the internal arrays
   * after matching has occured
   */
  private function updateItemStatus($updateType)
  {
    $trans = Yii::app()->db->beginTransaction();
    try {
      $model = feedItem::model();
      // The status of downloaded items have already been set.
      if(count($this->toQueue) !== 0)
        $model->updateByPk($this->toQueue, array('status'=>feedItem::STATUS_QUEUED));
      if(count($this->duplicates) !== 0) {
        Yii::log('Marking duplicate feeditems: '.print_r($this->duplicates, true), CLogger::LEVEL_INFO);
        $model->updateByPk($this->duplicates, array('status'=>feedItem::STATUS_DUPLICATE));
      }

      // if we were checking new items, change all still new to nomatch
      if($updateType === feedItem::STATUS_NEW) {
        $model->updateAll(
            array('status'=>feedItem::STATUS_NOMATCH),
            'status = :status',
            array(':status'=>feedItem::STATUS_NEW)
        );
      }
      $trans->commit();
    } catch (Exception $e) {
      $trans->rollback();
      throw $e;
    }

    $this->duplicates = $this->toQueue = array();
  }

  /**
   * Reset any currently matching items to nomatch
   * @param BaseFavorite favorite to reset the matches of
   */
  public function resetMatching($favorite)
  {
    if(!is_subclass_of($favorite, 'BaseFavorite'))
      throw new CException("Argument must be a subclass of BaseFavorite. Given:  ".get_class($favorite));
    $transaction=$favorite->getDbConnection()->beginTransaction();
    try {
      $table = $favorite->tableName();
      $favorite->dbConnection->createCommand(
          'UPDATE feedItem SET status='.feedItem::STATUS_NOMATCH.
          ' WHERE feedItem.id IN ( SELECT feedItem_id as id FROM matching'.$table.' m'.
                                  ' WHERE m.'.$table.'_id = '.$favorite->id.
                                  '   AND m.feedItem_status NOT IN ("'.
                                    feedItem::STATUS_AUTO_DL.'", "'.feedItem::STATUS_MANUAL_DL.'"));'
      )->execute();
      $transaction->commit();
    }
    catch (Exception $e)
    {
      $transaction->rollback();
      throw $e;
    }
  }

}

