<?php

class downloadManager extends favoriteManager {
  private $_nzbClient, $_torClient;

  private $opts; // the current item being started in the form of either
                 // a feedItem or a row from a matchingFavorite* view


  // used for error reporting purposes
  public function attributeNames() {
    return array(
        "downloadType",
        "client",
    );
  }

  public function safeAttributes() {
    return array(
    );
  }

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

    return isset($this->opts['favoriteTvShows_id']) ? 'tvShow' :
           isset($this->opts['favoriteMovies_id']) ? 'Movie' :
           isset($this->opts['favoriteStrings_id']) ? 'String' : null;
  }

  public function getItemTypeRecord() {
    if(is_array($this->opts))
    {
      $class = 'favorite'.ucwords($this->getFavoriteType());
      if($class !== 'favorite')
        return CActiveRecord::model($class)->findByPk($this->opts[$class.'s_id']);
      else
        return null;
    }
    else
    {
      return $this->opts->itemTypeRecord;
    }
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
    $clientMap = array(
        feedItem::TYPE_TORRENT=>'torClient',
        feedItem::TYPE_NZB=>'nzbClient',
    );

    if(isset($clientMap[$type]))
    {
      $attr = $clientMap[$type];
      $_attr = '_'.$attr;
      if($this->$_attr === null)
      {
        $class = Yii::app()->dvrConfig->$attr;
        if(in_array($class, array_keys($this->availClients[$type]))) 
          $this->$_attr = new $class($this);
      }
      return $this->$_attr;
    }
    $this->addError("downloadType", "Download type of the feedItem is invalid.");
    Yii::log("Unknown download type\n".print_r($this->opts, true), CLogger::LEVEL_ERROR);
  }

  public function afterDownload()
  {
    $transaction = Yii::app()->db->beginTransaction();
    try {
      // mark queued items to duplicate if they match the started item
      Yii::app()->db->createCommand(
          'UPDATE feedItem'.
          '   SET status = '.feedItem::STATUS_DUPLICATE.
          ' WHERE status = '.feedItem::STATUS_QUEUED.
          '   AND EXISTS( SELECT two.id'.
          '                 FROM feedItem two'.
          '                WHERE two.id = '.$this->feedItemId.
          '                  AND (    (feedItem.movie_id NOT NULL AND feedItem.movie_id = two.movie_id )'.
          '                        OR (feedItem.other_id NOT NULL AND feedItem.other_id = two.other_id )'.
          '                        OR (feedItem.tvEpisode_id NOT NULL AND feedItem.tvEpisode_id = two.tvEpisode_id )'.
          '                      )'.
          '              );'
      )->execute();
  
      // create a new history record for this download
      $history = new history;
      $history->feedItem_id = $this->feedItemId;
      $history->feedItem_title = $this->title;
      $history->feed_id = $this->feedId;
      $history->feed_title = $this->feedTitle;
      $history->favorite_name = $this->favoriteName;
      $history->favorite_type = $this->favoriteType;
      $history->save();
  
      // mark the tvEpisode/movie/other as STATUS_DOWNLOADED
      $record = $this->itemTypeRecord;
      if($record) 
      {
        $class = get_class($record);
        $record->status = constant("$class::STATUS_DOWNLOADED");
        $record->save();
      }
      $transaction->commit();
    } catch ( Exception $e ) {
      $transaction->rollback();
      throw $e;
    }
  }

  public function beforeDownload()
  {
    return True;
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
    Yii::log('Starting download');
    $error = False;

    // $opts is used in the various get functions to make the following code cleaner
    $this->opts = $opts;

    $client = $this->getClient();
    if($this->beforeDownload())
    {
      if(is_object($client) ? $client->addByUrl($this->url) : False)
        $this->afterDownload($success);
      else
      {
        $error = true;
        $this->addError("client", (is_object($client) ? $client->getError() : 'Unable to initialize client'));
        $status = feedItem::STATUS_FAILED_DL;
      }
      // not in afterDownload to allow failure to set STATUS_FAILED_DL
      Yii::log("Setting feedItem {$this->feedItemId} to $status");
      feedItem::model()->updateByPk($this->feedItemId, array('status'=>$status));
    }

    // reset the options to null incase anything trys to access the stale information
    $this->opts = null;

    return !$error;
  }

}

