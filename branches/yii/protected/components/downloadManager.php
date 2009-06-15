<?php

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
   * finds the feeditem id of the current item being started
   * @param none
   */
  public function getFeedItemId() {
    return is_a($this->opts, 'feedItem') ? $this->opts->id : $this->opts['feedItem_id'];
  }

  /**
   * finds the other id of the current item being started
   * @param none
   */
  public function getOtherId() {
    return is_a($this->opts, 'feedItem') ? $this->opts->other_id : isset($this->opts['other_id']) ? $this->opts['other_id'] : False;
  }

  /**
   * finds the movie id of the current item being started
   * @param none
   */
  public function getMovieId() {
    return is_a($this->opts, 'feedItem') ? $this->opts->movie_id : isset($this->opts['movie_id']) ? $this->opts['movie_id'] : False;
  }

  /**
   * finds the tvEpisode id of the current item being started
   * @param none
   */
  public function getTvEpisodeId() {
    return is_a($this->opts, 'feedItem') ? $this->opts->tvEpisode_id : isset($this->opts['tvEpisode_id']) ? $this->opts['tvEpisode_id'] : False;
  }

  /**
   * finds the url of the current item being started
   * @param none
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
   * finds the title of the current item being started
   * @param none
   */
  public function getTitle() {
    return is_array($this->opts) ? $this->opts['feedItem_title'] : $this->opts->title;
  }

  /**
   * finds the download type of the current item being started
   * @param none
   */
  public function getDownloadType() {
    return is_array($this->opts) ? $this->opts['feedItem_downloadType'] : $this->opts->downloadType;
  }

  public function getFeedId() {
    return is_array($this->opts) ? $this->opts['feed_id'] : $this->opts->feed_id;
  }

  public function getFeedTitle() {
    return is_array($this->opts) ? $this->opts['feed_title'] : $this->opts->feed->title;
  }

  public function getFavoriteName() {
    return is_array($this->opts) ? $this->opts['favorite_name'] : 'Manual DL';
  }

  public function getFavoriteType() {
    if(!is_array($this->opts))
      return null;

    return isset($this->opts['favoriteTvShows_id']) ? 'tvEpisode' :
           isset($this->opts['favoriteMovies_id']) ? 'Movie' :
           isset($this->opts['favoriteStrings_id']) ? 'String' : null;
  }

  /**
   * list of classes and names of available torrent clients
   */
  public function getAvailClients() {
    return array(
        feedItem::TYPE_TORRENT => array(
            'clientBTPD'     => 'BTPD',
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

  public function getClient() 
  {
    $type = $this->downloadType;
    switch($type) 
    {
      case feedItem::TYPE_TORRENT:
        if($this->_torClient === null) 
        {
          $class = Yii::app()->dvrConfig->torClient;
          if(in_array($class, array_keys($this->availClients[$type])))
            $this->_torClient = new $class($this);
        }
        return $this->_torClient;
        break;
      case feedItem::TYPE_NZB:
        if($this->_nzbClient === null) 
        {
          $class = Yii::app()->dvrConfig->nzbClient;
          if(in_array($class, array_keys($this->availClients[$type]))) 
            $this->_nzbClient = new $class($this);
        }
        return $this->_nzbClient;
        break;
    }
    Yii::log("Unknown download type\n".print_r($this->opts, true), CLogger::LEVEL_ERROR);
  }

  public function getErrors() 
  {
    return $this->errors;
  }

  /**
   * Required to be initialized as a Yii Component and accessed as Yii::app()->objectname
   */
  public function init() 
  {
  }

  /**
   * starts a download in the proper download client
   * @param mixed either a feedItem object or a row returned from the various matching views in the db
   * @param integer the status to set related feeditem to on successfull start,.  from feedItem::STATUS_*
   */
  public function startDownload($opts, $status) 
  {
    Yii::log('Starting download', CLogger::LEVEL_ERROR);
    $error = False;
    // $opts is used in the various get functions to make the following code cleaner
    $this->opts = $opts;

    $success = $this->client->addByUrl($this->url);

    if($success) 
    {
      $history = new history;
      $history->feedItem_id = $this->feedItemId;
      $history->feedItem_title = $this->title;
      $history->feed_id = $this->feedId;
      $history->feed_title = $this->feedTitle;
      $history->favorite_name = $this->favoriteName;
      $history->favorite_type = $this->favoriteType;
      $history->save();

      // Update status as neccessary
      if(is_numeric($tvEpisodeId = $this->tvEpisodeId)) 
      {
        Yii::log("Setting tvEpisode $tvEpisodeId to STATUS_DOWNLOADED", CLogger::LEVEL_ERROR);
        tvEpisode::model()->updateByPk(
            $tvEpisodeId, array('status'=>tvEpisode::STATUS_DOWNLOADED)
        );
        
      } 
      elseif(is_numeric($movieId = $this->movieId)) 
      {
        Yii::log("Setting movie $movieId to STATUS_DOWNLOADED", CLogger::LEVEL_ERROR);
        movie::model()->updateByPk(
            $movieId, array('status' => movie::STATUS_DOWNLOADED)
        );
      } 
      elseif(is_numeric($otherId = $this->otherId)) 
      {
        Yii::log("Setting other $otherId to STATUS_DOWNLOADED", CLogger::LEVEL_ERROR);
        other::model()->updateByPk(
            $otherId, array('status' => other::STATUS_DOWNLOADED)
        );
      } 
      else 
      {
        Yii::log("Success starting, but Unknown feeditem type in startDownload\n".print_r($this->opts, true), CLogger::LEVEL_ERROR);
      }
    } 
    else 
    {
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

