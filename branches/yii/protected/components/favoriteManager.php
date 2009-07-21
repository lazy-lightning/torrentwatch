<?php

// client class accessed by controllers to start downloads
abstract class favoriteManager extends CModel {
  abstract function startDownload($opts, $status);

  private $toStart = array();
  private $toQueue = array();
  private $duplicates = array();

  // there must be a better way than the following for multiple favorites . . .
  /**
   * looks for feedItems that matching a favorite in the database
   * @param integer a feeditem status to limit the search to
   */
  public function checkFavorites($itemStatus = feedItem::STATUS_NEW) 
  {
    Yii::trace('Checking for matching favorites');
    $transaction = Yii::app()->db->beginTransaction();
    try {
      $this->checkTvShowFavorites($itemStatus);
      $this->checkMovieFavorites($itemStatus);
      $this->checkStringFavorites($itemStatus);
      $this->updateItemStatus($itemStatus);
      $transaction->commit();
    } catch ( Exception $e ) {
      $transaction->rollback();
      throw $e;
    }

    $this->startDownloads();
  }
 
  /**
   * looks for feedItems that matching a favoriteMovie in the database
   * @param integer a feeditem status to limit the search to
   */
  private function checkMovieFavorites($itemStatus = feedItem::STATUS_NEW) 
  {
    Yii::log('Looking for movie favorites');
    $db = Yii::app()->db;

    // Mark any previously downloaded movies that are now matching
    $db->createCommand(
      'UPDATE feedItem'.
      '   SET status='.feedItem::STATUS_DUPLICATE.
      ' WHERE feedItem.status = '.$itemStatus.
      '   AND feedItem.id IN ( SELECT feedItem_id'.
                            '    FROM matchingFavoriteMovies m'.
                            '   WHERE m.movie_status = '.movie::STATUS_DOWNLOADED.
                            ');'
    )->execute();

    // Get any matching items from the db
    $reader = $db->createCommand(
        'SELECT * FROM matchingFavoriteMovies'.
        ' WHERE feedItem_status='.$itemStatus.
        '   AND movie_status='.movie::STATUS_NEW
    )->query();

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
  private function checkStringFavorites($itemStatus = feedItem::STATUS_NEW) 
  {
    Yii::log('Looking for string favorites');
    $db = Yii::app()->db;

    $reader = $db->createCommand(
        'SELECT * FROM matchingFavoriteStrings'.
        '  WHERE feedItem_status='.$itemStatus
    )->query();

    foreach($reader as $row) 
    {
      if($row['favorite_queue'] == 1)
        $this->toQueue[] = $row['feedItem_id'];
      else
        $this->toStart[][] = $row;
    }
  }

  /**
   * looks for feedItems that matching a favoriteTvShow in the database
   * @param integer a feeditem status to limit the search to
   */
  private function checkTvShowFavorites($itemStatus = feedItem::STATUS_NEW) 
  {
    Yii::log('Looking for TvShow favorites');
    $db = Yii::app()->db;

    // Mark any duplicate episodes
    $db->createCommand(
        'UPDATE feedItem'.
        '   SET status='.feedItem::STATUS_DUPLICATE.
        ' WHERE feedItem.status = '.$itemStatus.
        '   AND feedItem.id IN ( SELECT feedItem_id'.
                              '    FROM matchingFavoriteTvShows m'.
                              '   WHERE m.tvEpisode_status = '.tvEpisode::STATUS_DOWNLOADED.
                              ');'
    )->execute();

    // Mark any old episodes with favorite set to only newer episodes(handled inside the view)
    $db->createCommand(
        'UPDATE feedItem'.
        '   SET status='.feedItem::STATUS_OLD.
        ' WHERE feedItem.status = '.$itemStatus.
        '   AND feedItem.id IN ( SELECT feedItem_id'.
                              '    FROM onlyNewerFeedItemFilter'.
                              ')'
    )->execute();

    // get any matching items with the right itemStatus
    $season = $episode = 0;
    $reader = $db->createCommand(
        'SELECT * FROM matchingFavoriteTvShows'.
        ' WHERE feedItem_status='.$itemStatus.
        '   AND tvEpisode_status='.tvEpisode::STATUS_NEW
    )->query();

    // loop through the matching items
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
   */
  private function updateItemStatus($updateType)
  {
    // After matching has occured, updated item statuses
    // The status of downloaded items have not yet been set so safe to set them and
    // allow startDownload to set them properly
    if(count($this->toQueue) !== 0)
      feedItem::model()->updateByPk($this->toQueue, array('status'=>feedItem::STATUS_QUEUED));
    if(count($this->duplicates) !== 0)
      feedItem::model()->updateByPk($this->duplicates, array('status'=>feedItem::STATUS_DUPLICATE));

    // if we were checking new items, change all still new to nomatch
    if($updateType === feedItem::STATUS_NEW) 
    {
      feedItem::model()->updateAll(
          array('status'=>feedItem::STATUS_NOMATCH),
          'status = :status',
          array(':status'=>feedItem::STATUS_NEW)
      );
    }
    
    $this->duplicates = $this->toQueue = array();
  }

  /**
   * Reset any currently matching items to nomatch
   * @param BaseFavorite favorite to reset the matches of
   */
  public function resetMatching($favorite)
  {
    if(is_subclass_of($favorite, 'BaseFavorite'))
    {
      $table = $favorite->tableName();
      $favorite->dbConnection->createCommand(
          'UPDATE feedItem SET status='.feedItem::STATUS_NOMATCH.
          ' WHERE feedItem.id IN ( SELECT feedItem_id as id FROM matching'.$table.' m'.
                                  ' WHERE m.'.$table.'_id = '.$favorite->id.
                                  '   AND m.feedItem_status NOT IN ("'.
                                    feedItem::STATUS_AUTO_DL.'", "'.feedItem::STATUS_MANUAL_DL.'"));'
      )->execute();
    }
  }

}

