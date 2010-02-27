<?php

// not sure this class heirarchy makes sense
class downloadManager extends favoriteManager {
  private $_nzbClient, $_torClient;

  private $opts; // the current item being started in the form of either
                 // a feedItem or a row from a matchingFavorite* view

  /**
   * getters 
   * 
   * @var array a list of variable getters specifying the attribute or key
   *            to use if $this->opts is a feedItem object or an array
   *            ( variable => ( 'obj'=> attribute, 'arr'=>key )
   */
  private $getters = array(
      'downloadType' => array('obj'=>'downloadType',       'arr'=>'feedItem_downloadType'),
      'feedId'       => array('obj'=>'feed_id',            'arr'=>'feed_id'),
      'feedItemId'   => array('obj'=>'id',                 'arr'=>'feedItem_id'),
      'feedTitle'    => array('obj'=>array('feed','title'),'arr'=>'feed_title'),
      'movieId'      => array('obj'=>'movie_id',           'arr'=>'movie_id'),
      'otherId'      => array('obj'=>'other_id',           'arr'=>'other_id'),
      'title'        => array('obj'=>'title',              'arr'=>'feedItem_title'),
      'tvEpisodeId'  => array('obj'=>'tvEpisode_id',       'arr'=>'tvEpisode_id'),
  );

  /**
   * started 
   * 
   * @var array an array of history items for items started in this run ( history )
   */
  private $started = array();

  public function __get($name)
  {
    if(isset($this->getters[$name]))
    {
      if(is_array($this->opts))
        return isset($this->opts[$this->getters[$name]['arr']]) ? 
            $this->opts[$this->getters[$name]['arr']] : false;
      else 
      {
        $attr = $this->getters[$name]['obj'];
        if(!is_array($attr))
          return $this->opts->$attr;
        else
        {
          // FIXME: uninspired . .
          // allows selecting more than one level deep
          // shifting wont effect the origional array, php makes
          // copies of arrays by default.
          $foo = $this->opts->{array_shift($attr)};
          foreach($attr as $i)
            $foo = $foo->$i;
          return $foo;
        }
      }
    }
    return parent::__get($name);
  }

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
   * @return mixed the options of the current item being started
   * @param none
   */
  public function getOpts() {
    return $this->opts;
  }


  /**
   * @return string url of the current item being started
   * @param none
   */
  public function getUrl() {
    $is_a = ($this->opts instanceOf feedItem);
    $url = $is_a ? $this->opts->url : $this->opts['feedItem_url'];
    $feed_url = $is_a ? $this->opts->feed->url : $this->opts['feed_url'];
    
    if(($cookies = stristr($feed_url, ':COOKIE:'))) {
      $url .= $cookies;
    }
    return $url;
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
   * @ return array (feedItem type => (class => displayName) )
   */
  public function getAvailClients() {
    return array(
        feedItem::TYPE_TORRENT => array(
            'clientBTPD'     => 'BTPD',
            'clientCTorrent' => 'CTorrent w/DCTCS',
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
   * getStarted 
   * 
   * @var array an array of history objects for items started in this instance ( history )
   */
  public function getStarted()
  {
    return $this->started;
  }

  /**
   * Runs actions to take place on successfull start of a download
   * @return none
   */
  public function afterDownload()
  {
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
    if($history->save())
      $this->started[] = $history;
    else
      Yii::log('Error saving history: '.print_r($history->getErrors(), true), CLogger::LEVEL_ERROR);

    // mark the tvEpisode/movie/other as STATUS_DOWNLOADED
    $record = $this->itemTypeRecord;
    if($record) 
    {
      $class = get_class($record);
      Yii::log('Updating related '.$class);
      $record->updateByPk($record->id, array('status'=>constant("$class::STATUS_DOWNLOADED")));
    }
    else
      Yii::log('WTF dude weve hit an inconsistancy, no related record for '.$this->feedItemId, CLogger::LEVEL_ERROR);

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
   * @return boolean weather the download successfully started
   */
  // TODO: Perhaps should be reworked to allow for downloading from the net
  //       outside of a locking transaction
  public function startDownload($opts, $status) 
  {
    // $opts is used in the various get functions to make the following code cleaner
    $this->opts = $opts;

    Yii::trace("Starting download: ".$this->title);

    if($this->beforeDownload())
    {
      $transaction = Yii::app()->db->beginTransaction();
      try {
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
        $transaction->commit();
      } catch ( Exception $e ) {
        $transaction->rollback();
        throw $e;
      }
    }

    // reset the options to null incase anything trys to access the stale information
    $this->opts = null;

    return $status !== feedItem::STATUS_FAILED_DL;
  }

}

