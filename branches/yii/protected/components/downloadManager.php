<?php

// client class accessed by controllers to start downloads
abstract class favoriteManager extends CComponent {
  abstract function startDownload($opts, $status);

  // there must be a better way than the following for multiple favorites . . .
  /**
   * looks for feedItems that matching a favorite in the database
   * @param integer a feeditem status to limit the search to
   */
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
 
  /**
   * looks for feedItems that matching a favoriteMovie in the database
   * @param integer a feeditem status to limit the search to
   */
  public function checkMovieFavorites($itemStatus = feedItem::STATUS_NEW) {
  }

  /**
   * looks for feedItems that matching a favoriteString in the database
   * @param integer a feeditem status to limit the search to
   */
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

  /**
   * looks for feedItems that matching a favoriteTvShow in the database
   * @param integer a feeditem status to limit the search to
   */
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
        '  WHERE feedItem_status='.$itemStatus)->query();

    // loop through the matching items
    // and group the duplicates by tvEpisode_id
    $toStart = array();
    foreach($reader as $row) {
      $toStart[$row['tvEpisode_id']][] = $row;
    }
    unset($row);

    foreach($toStart as $items) {
      // For now just take the first item of each tvepisode, but this
      // structure allows there to be a decision making process inserted
      // here to decide which item based on feed priority or something.
      $success = false;
      foreach($items as $item) {
        if($success) {
          $duplicates[] = $item['feedItem_id'];
        } else {
          $success = $this->startDownload($item, feedItem::STATUS_AUTO_DL);
        }
      }
    }

    // After matching has occured, updated item statuses
    if(count($duplicates) !== 0) // mark feedItems as duplicate if another feedItem of the same season and episode
      feedItem::model()->updateByPk($duplicates, array('status'=>feedItem::STATUS_DUPLICATE)); // has been downloaded
  }

}

class downloadManager extends favoriteManager {
  private $errors = array();
  private $_nzbClient, $_torClient;

  private $opts;

  /**
   * returns the options of the current item being started
   * @param none
   */
  public function getOpts() {
    return $this->opts;
  }

  /**
   * finds the feeditem id from the passed download options
   * @param mixed usually a row from a matchingFavorites view
   *               it could also be a feed Item
   */
  public function getFeedItemId() {
    return is_a($this->opts, 'feedItem') ? $this->opts->id : $this->opts['feedItem_id'];
  }

  /**
   * finds the other id from the passed download options
   * @param mixed usually a row from a matchingFavorites view
   *               it could also be a feed Item
   */
  public function getOtherId() {
    return is_a($this->opts, 'feedItem') ? $this->opts->other_id : isset($this->opts['other_id']) ? $this->opts['other_id'] : False;
  }

  /**
   * finds the movie id from the passed download options
   * @param mixed usually a row from a matchingFavorites view
   *               it could also be a feed Item
   */
  public function getMovieId() {
    return is_a($this->opts, 'feedItem') ? $this->opts->movie_id : isset($this->opts['movie_id']) ? $this->opts['movie_id'] : False;
  }

  /**
   * finds the tvEpisode id from the passed download options
   * @param mixed usually a row from a matchingFavorites view
   *               it could also be a feed Item
   */
  public function getTvEpisodeId() {
    return is_a($this->opts, 'feedItem') ? $this->opts->tvEpisode_id : isset($this->opts['tvEpisode_id']) ? $this->opts['tvEpisode_id'] : False;
  }

  /**
   * finds the url from the passed download options
   * @param mixed usually a row from a matchingFavorites view
   *               it could also be a feed Item
   */
  public function getUrl() {
    if(is_a($this->opts, 'feedItem')) {
      $url = $this->opts->url;
      $feed_url = $this->opts->feed->url;
    } else {
      $url = $this->opts['feedItem_url'];
      $feed_url = $this->opts['feed_url'];
    }

    if($cookies = stristr($feed_url, ':COOKIE:')) {
      $url .= $cookies;
    }
    return $url;
  }

  /**
   * finds the title from the passed download options
   * @param mixed usually a row from a matchingFavorites view
   *               it could also be a feed Item
   */
  public function getTitle() {
    return is_array($this->opts) ? $this->opts['feedItem_title'] : $this->opts->title;
  }

  /**
   * finds the download type from the passed download options
   * @param mixed usually a row from a matchingFavorites view
   *               it could also be a feed Item
   */
  public function getDownloadType() {
    return is_array($this->opts) ? $this->opts['feedItem_downloadType'] : $this->opts->downloadType;
  }

  /**
   * list of classes and names of available torrent clients
   */
  public function getAvailClients() {
    return array(
        feedItem::TYPE_TORRENT => array(
            'clientBTPD'     => 'BTPD',
            'clientCTorrent' => 'cTorrent',
            'clientTrans122' => 'Transmission 1.22',
            'clientTransRPC' => 'Transmission >= 1.3',
            'clientFolder'   => 'Save to Folder',
        ),
        feedItem::TYPE_NZB => array(
            'clientNZBGet'   => 'NZBGet',
            'clientSABnzbd'  => 'SABnzbd+',
            'clientFolder'   => 'Save to Folder',
        ),
    );
  }

  public function getClient() {
    $type = $this->downloadType;
    switch($type) {
      case feedItem::TYPE_TORRENT:
        if($this->_torClient === null) {
          $class = Yii::app()->dvrConfig->torClient;
          if(in_array($class, array_keys($this->availClients[$type])))
            $this->_torClient = new $class($this);
        }
        return $this->_torClient;
        break;
      case feedItem::TYPE_NZB:
        if($this->_nzbClient === null) {
          $class = Yii::app()->dvrConfig->nzbClient;
          if(in_array($class, array_keys($this->availClients[$type]))) {
            $this->_nzbClient = new $class($this);
          }
        }
        return $this->_nzbClient;
        break;
    }
    Yii::log("Unknown download type\n".print_r($this->opts, true), CLogger::LEVEL_ERROR);
  }

  public function getErrors() {
    return $this->errors;
  }

  /**
   * Required to be initialized as a Yii Component and accessed as Yii::app()->objectname
   */
  public function init() {
  }

  /**
   * starts a download in the proper download client
   * @param mixed either a feedItem object or a row returned from the various matching views in the db
   * @param integer the status to set related feeditem to on successfull start,.  from feedItem::STATUS_*
   */
  public function startDownload($opts, $status) {
    $error = False;
    // $opts is used in the various get functions to make the following code cleaner
    $this->opts = $opts;

    $success = $this->client->addByUrl($this->url);

    // Update status as neccessary
    if($success) {
      if(is_numeric($tvEpisode_id = $this->tvEpisodeId)) {
        tvEpisode::model()->updateByPk(
            $tvEpisodeId, array('status'=>tvEpisode::STATUS_DOWNLOADED)
        );
      } elseif(is_numeric($movieId = $this->movieId)) {
        movie::model()->updateByPk(
            $movieId, array('status' => movie::STATUS_DOWNLOADED)
        );
      } elseif(is_numeric($otherId = $this->otherId)) {
        other::model()->updateByPk(
            $otherId, array('status' => other::STATUS_DOWNLOADED)
        );
      } else {
        Yii::log("Unknown feeditem type in startDownload\n".print_r($this->opts, true), CLogger::LEVEL_ERROR);
      }
    }  else {
      $error = $this->errors[] = $this->client->getError();
      $status = feedItem::STATUS_FAILED_DL;
      Yii::log("Failed starting download: ".$error, CLogger::LEVEL_ERROR);
    }

    feedItem::model()->updateByPk($this->feedItemId, array('status'=>$status));

    // reset the options to null incase anything trys to access the stale information
    $this->opts = null;

    return $success;
  }
}

