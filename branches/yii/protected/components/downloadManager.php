<?php

// client class accessed by controllers to start downloads
class downloadManager extends CComponent {
  private $errors = array();
  private $_client;

  // $opts is usually a row from the matchingFeedItems view
  // it could also be a feed Item
  public static function findId($opts) {
    return is_a($opts, 'feedItem') ? $opts->id : $opts['feedItem_id'];
  }

  public static function findOtherId($opts) {
    return is_a($opts, 'feedItem') ? $opts->other_id : isset($opts['other_id']) ? $opts['other_id'] : False;
  }

  public static function findMovieId($opts) {
    return is_a($opts, 'feedItem') ? $opts->movie_id : isset($opts['movie_id']) ? $opts['movie_id'] : False;
  }

  public static function findTvEpisodeId($opts) {
    return is_a($opts, 'feedItem') ? $opts->tvEpisode_id : isset($opts['tvEpisode_id']) ? $opts['tvEpisode_id'] : False;
  }

  public static function findUrl($opts) {
    if(is_a($opts, 'feedItem')) {
      $url = $opts->url;
      $feed_url = $opts->feed->url;
    } else {
      $url = $opts['feedItem_url'];
      $feed_url = $opts['feed_url'];
    }

    if($cookies = stristr($feed_url, ':COOKIE:')) {
      $url .= $cookies;
    }
    return $url;
  }

  public function getAvailClients() {
    return array(
    //  Class(32chars max)    Name 
        'clientBTPD'     => 'BTPD',
        'clientTrans122' => 'Transmission 1.22',
        'clientTransRPC' => 'Transmission >= 1.3',
        'clientNZBGet'   => 'NZBGet',
        'clientSABnzbd'  => 'SABnzbd+',
        'clientFolder'   => 'Simple Folder',
    );
  }

  public function getClient() {
    if($this->_client === null) {
      $class = Yii::app()->dvrConfig->client;
      $this->_client = new $class;
    }
    return $this->_client;
  }

  public function getErrors() {
    return $this->errors;
  }

  // there must be a better way
  public function checkFavorites($itemStatus = feedItem::STATUS_NEW) {
    $this->checkTvShowFavorites($itemStatus);
    $this->checkMovieFavorites($itemStatus);
    $this->checkStringFavorites($itemStatus);

    // if we were checking new items, change all still new to nomatch
    if($itemStatus === feedItem::STATUS_NEW) {
      feedItem::model()->updateAll(
          array('status'=>feedItem::STATUS_NOMATCH),
          'status = :status',
          array(':status'=>feedItem::STATUS_NEW)
      );
    }
  }

  public function checkMovieFavorites($itemStatus = feedItem::STATUS_NEW) {
  }

  public function checkStringFavorites($itemStatus = feedItem::STATUS_NEW) {
    // db view not completed
    return;

    $dldEpisodes = array();

    $reader = Yii::app()->db->createCommand(
        'SELECT * FROM matchingFavoriteStrings'.
        '  WHERE feedItem_status='.$itemStatus)->query();

    foreach($reader as $row) {
      $this->startDownload($row, feedItem::STATUS_AUTO_DL);
    }
  }

  public function checkTvShowFavorites($itemStatus = feedItem::STATUS_NEW) {
    $duplicates = $old = $dldEpisodes = array();
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

    // get any matching items with the right itemStatus
    $season = $episode = 0;
    $reader = $db->createCommand(
        'SELECT * FROM matchingFavoriteTvShows'.
        '  WHERE feedItem_status='.$itemStatus)->query();

    foreach($reader as $row) {
      if(in_array($row['tvEpisode_id'], $dldEpisodes)) {
        Yii::log("Marking feedItem {$row['feedItem_id']} as Duplicate", CLogger::LEVEL_ERROR);
        $duplicates[] = $row['feedItem_id'];
        continue;
      } 

      if($row['favorite_onlyNewer'] == True) {
        $season = $row['season'];
        $episode = $row['episode'];
        $tvShow_id = $row['tvShow_id'];
        if(!isset($onlyNewer)) {
          $onlyNewer = Yii::app()->db->createCommand(
              "SELECT EXISTS(SELECT id FROM newestTvEpisode WHERE tvShow_id=:tvShow_id AND "
             ." ( season < :season OR"
             ."  (season = :season AND episode < :episode) ));");
          $onlyNewer->bindParam(':season', $season);
          $onlyNewer->bindParam(':episode', $episode);
          $onlyNewer->bindParam(':tvShow_id', $tvShow_id);
        }
        if($onlyNewer->queryScalar() === False) {
          Yii::log("Marking feedItem {$row['feedItem_id']} as Old", CLogger::LEVEL_ERROR);
          $old[] = $row['feedItem_id'];
          continue;
        }
      }

      // Passed all filters, do the download
      // This function is !SLOW! because it connects over the internet for the file.
      // possible concurency issues between now and when status update occurs
      // on feed items?
      if($this->startDownload($row, feedItem::STATUS_AUTO_DL)) {
        $dldEpisodes[] = $row['tvEpisode_id'];
      }
    }

    // After matching has occured, updated item statuses
    if(count($old) !== 0) // mark feedItems as old if their favorite only matches new episodes
      feedItem::model()->updateByPk($old, array('status'=>feedItem::STATUS_OLD));
    if(count($duplicates) !== 0) // mark feedItems as duplicate if another feedItem of the same season and episode
      feedItem::model()->updateByPk($duplicates, array('status'=>feedItem::STATUS_DUPLICATE)); // has been downloaded
  }

  public function init() {
  }

  public function startDownload($opts, $status) {
    $error = False;

    $success = $this->client->addByUrl($this->findUrl($opts), $opts);

    // Update status as neccessary
    if($success) {
      if(is_numeric($tvEpisode_id = $this->findTvEpisodeId($opts))) {
        tvEpisode::model()->updateByPk(
            $tvEpisodeId,
            array('status'=>tvEpisode::STATUS_DOWNLOADED)
        );
      } elseif(is_numeric($movieId = $this->findMovieId($opts))) {
        movie::model()->updateByPk(
            $movieId, array('status' => movie::STATUS_DOWNLOADED)
        );
      } elseif(is_numeric($otherId = $this->findOtherId($opts))) {
        other::model()->updateByPk(
            $otherId, array('status' => other::STATUS_DOWNLOADED)
        );
      }
    }  else {
      $error = $this->errors[] = $this->client->getErrors();
      $status = feedItem::STATUS_FAILED_DL;
      Yii::log("Failed starting download: ".$error, CLogger::LEVEL_ERROR);
    }

    feedItem::model()->updateByPk($this->findId($opts), array('status'=>$status));

    return $success;
  }
}

