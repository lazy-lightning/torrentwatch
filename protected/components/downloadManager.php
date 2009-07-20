<?php

class downloadManager extends favoriteManager {
  private $_nzbClient, $_torClient;

  private $opts; // the current item being started in the form of either
                 // a feedItem or a row from a matchingFavorite* view

  /**
   * @return array valid attribute names for error reporting purposes only
   */
  public function attributeNames() {
    return array(
        "downloadType",
        "client",
    );
  }

  /**
   * Attributes safe to be massively assigned
   * @return array
   */
  public function safeAttributes() {
    return array(
    );
  }

  /**
   * @return the options of the current item being started
   * @param none
   */
  public function getOpts() {
    return $this->opts;
  }

  /**
   * @return number feeditem id of the current item being started
   * @param none
   */
  public function getFeedItemId() {
    return is_a($this->opts, 'feedItem') ? $this->opts->id : $this->opts['feedItem_id'];
  }

  /**
   * @return number other id of the current item being started
   * @param none
   */
  public function getOtherId() {
    return is_a($this->opts, 'feedItem') ? $this->opts->other_id : isset($this->opts['other_id']) ? $this->opts['other_id'] : False;
  }

  /**
   * @return number movie id of the current item being started
   * @param none
   */
  public function getMovieId() {
    return is_a($this->opts, 'feedItem') ? $this->opts->movie_id : isset($this->opts['movie_id']) ? $this->opts['movie_id'] : False;
  }

  /**
   * @return number tvEpisode id of the current item being started
   * @param none
   */
  public function getTvEpisodeId() {
    return is_a($this->opts, 'feedItem') ? $this->opts->tvEpisode_id : isset($this->opts['tvEpisode_id']) ? $this->opts['tvEpisode_id'] : False;
  }

  /**
   * @return string url of the current item being started
   * @param none
   */
  public function getUrl() {
    $is_a = is_a($this->opts, 'feedItem');
    $url = $is_a ? $this->opts->url : $this->opts['feedItem_url'];
    $feed_url = $is_a ? $this->opts->feed->url : $this->opts['feed_url'];
    
    if($cookies = stristr($feed_url, ':COOKIE:')) {
      $url .= $cookies;
    }
    return $url;
  }

  /**
   * @return string title of the current item being started
   * @param none
   */
  public function getTitle() {
    return is_array($this->opts) ? $this->opts['feedItem_title'] : $this->opts->title;
  }

  /**
   * @return string download type of the current item being started
   * @param none
   */
  public function getDownloadType() {
    return is_array($this->opts) ? $this->opts['feedItem_downloadType'] : $this->opts->downloadType;
  }

  /**
   * @return number id of the feed related to the item being started
   * @param none
   */
  public function getFeedId() {
    return is_array($this->opts) ? $this->opts['feed_id'] : $this->opts->feed_id;
  }

  /**
   * @return string title of the feed related to the item being started
   * @param none
   */
  public function getFeedTitle() {
    return is_array($this->opts) ? $this->opts['feed_title'] : $this->opts->feed->title;
  }

  /**
   * @return string name of the favorite starting this download, or 'Manual DL'
   * @param none
   */
  public function getFavoriteName() {
    return is_array($this->opts) ? $this->opts['favorite_name'] : 'Manual DL';
  }

  /**
   * @return string the type of favorite starting this download
   * or null if not started by a favorite
   * @param none
   */
  public function getFavoriteType() {
    if(!is_array($this->opts))
      return null;


    return (isset($this->opts['favoriteTvShows_id']) ? 'tvShow' :
           (isset($this->opts['favoriteMovies_id']) ? 'Movie' :
           (isset($this->opts['favoriteStrings_id']) ? 'String' : null)));
  }

  /**
   * @return mixed contents of itemTypeRecord related to current item being started
   * @param none
   */
  public function getItemTypeRecord() {
    if(is_array($this->opts))
      // FIXME: lazy, but short and it works. Should be able to do this without loading everything
      $item = feedItem::model()->findByPk($this->opts['feedItem_id']);
    else
      $item = $this->opts;

    return $item->itemTypeRecord;
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

  /**
   * @return BaseClient object capable of starting the current item
   */
  public function getClient() 
  {
    $type = $this->downloadType;
    // maps client types to their key in Yii::app()->dvrConfig
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

  /**
   * Runs actions to take place on successfull start of a download
   * @return none
   */
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
        Yii::log('Updating related '.$class);
        $record->updateByPk($record->id, array('status'=>constant("$class::STATUS_DOWNLOADED")));
      }
      else
        Yii::log('WTF, no related record for '.$this->feedItemId, CLogger::LEVEL_ERROR);

      $transaction->commit();
    } catch ( Exception $e ) {
      $transaction->rollback();
      throw $e;
    }
  }

  /**
   * Runs actions to take place before each download is started
   * @return boolean if the download should take place
   */
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
   * Entry point for downloading a feed item with a download client
   * @param mixed $opts either a feedItem object or a row returned from the various matching views in the db
   * @param integer $status the status to set related {@link feedItem} to on successfull start,.
   */
  public function startDownload($opts, $status) 
  {
    // $opts is used in the various get functions to make the following code cleaner
    $this->opts = $opts;

    Yii::trace("Starting download: ".$this->title);

    if($this->beforeDownload())
    {
      $client = $this->getClient();
      if(is_object($client) ? $client->addByUrl($this->url) : False)
        $this->afterDownload();
      else
      {
        $this->addError("client", (is_object($client) ? $client->getError() : 'Unable to initialize client'));
        $status = feedItem::STATUS_FAILED_DL;
      }
      // not in afterDownload to allow failure to set STATUS_FAILED_DL
      Yii::log("Updating {$this->title} to status ".feedItem::getStatusText($status), CLogger::LEVEL_INFO);
      feedItem::model()->updateByPk($this->feedItemId, array('status'=>$status));
    }

    // reset the options to null incase anything trys to access the stale information
    $this->opts = null;

    return $status !== feedItem::STATUS_FAILED_DL;
  }

}

