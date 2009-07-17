<?php

class AjaxController extends BaseController
{

  const ERROR_INVALID_ID = "Invalid ID paramater";

  /**
   * @var string specifies the default action to be 'list'.
   */
  public $defaultAction='fullResponce';

  /**
   * @var array valid favorite classes
   */
  private $favoriteWhitelist = array('favoriteTvShow', 'favoriteMovie', 'favoriteString');
  protected $responce = array();

  public function init()
  {
    parent::init();
    $this->layout = 'ajax';
  }

  /**
   * @return array action filters
   */
  public function filters()
  {
    return array(
      'accessControl', // perform access control for CRUD operations
    );
  }

  /**
   * Specifies the access control rules.
   * This method is used by the 'accessControl' filter.
   * @return array access control rules
   */
  public function accessRules()
  {
    return array(
/*      array('allow',  // allow all users
        'actions'=>array('fullResponce'),
        'users'=>array('*'),
      ), */
      array('allow', // allow authenticated user
        'actions'=>array(
            'fullResponce', 'dlFeedItem', 'saveConfig', 'addFeed', 'addFavorite', 'updateFavorite', 
            'inspect', 'clearHistory', 'createFavorite', 'deleteFavorite', 'loadFeedItems', 'resetData',
            'wizard',
        ),
        'users'=>array('@'),
      ),
      array('allow', // allow admin user 
        'actions'=>array(),
        'users'=>array('admin'),
      ),
      array('deny',  // deny all users
        'users'=>array('*'),
      ),
    );
  }

  public function loadFeedItem($id = null)
  {
    if($id === null)
      $id = isset($_GET['feedItem_id']) ? $_GET['feedItem_id'] : null;

    if(is_numeric($id))
      return feedItem::model()->with('quality')->findByPk($id);

    $this->responce['dialog']['error'] = true;
    $this->responce['dialog']['content'] = self::ERROR_INVALID_ID;
    return false;
  }

  /**
   * Creates a new Favorite based off of a feed item
   */
  public function actionAddFavorite()
  {
    $this->responce['dialog']['header'] = 'Add Favorite';

    $feedItem = $this->loadFeedItem();

    if($feedItem)
    {
      $fav = $feedItem->generateFavorite();
      $type=get_class($fav).'s';

      if($fav->save()) 
      {
        $this->responce['dialog']['content'] = 'New favorite successfully saved';
        $htmlId = $type.'-'.$fav->id;
      }
      else
      {
        $this->responce['dialog']['error'] = true;
        $this->responce['dialog']['content'] = 'Failure saving new favorite';
        $this->responce[$type.'-'] = $fav;
      }
      // After save to get the correct id
      $this->responce['showFavorite'] = '#'.$type.'-'.$fav->id;
      $this->responce['showTab'] = "#".$type;
    }

    $this->actionFullResponce();
  }

  protected function findFavoriteType() 
  {
    $class = null;
    foreach($this->favoriteWhitelist as $item) 
    {
      if(isset($_POST[$item])) 
        return $item;
    }

    $this->responce['dialog']['error'] = true;
    $this->responce['dialog']['content'] = 'Unknown favorite type';
    return false;
  }

  /**
   * updates a favorite based on $_POST data.  Called from action[Create|Update]Favorite
   * @param BaseFavorite the favorite to be updated
   */
  protected function updateFavorite($favorite)
  {
    $class = get_class($favorite);
    if(isset($_POST['quality_id']))
      $favorite->qualityIds = $_POST['quality_id'];

    $favorite->attributes = $_POST[$class];
    $favorite->save();
    // Tell the view to bring up the changed favorite
    $this->responce[$htmlId] = $favorite;
    $this->responce['showFavorite'] = "#".$class.'s-'.$favorite->id;
    $this->responce['showTab'] = "#".$class."s";
  }

  public function actionCreateFavorite()
  {
    $this->responce = array('dialog'=>array('header'=>'Create Favorite'));
    $class = $this->findFavoriteType();
    if($class)
    {
      Yii::trace('creating favorite');
      $this->updateFavorite(new $class);
    }

    $this->actionFullResponce();
  }

  public function actionUpdateFavorite()
  {
    $this->responce = array('dialog'=>array('header'=>'Update Favorite'));
    $class = $this->findFavoriteType();

    if($class && isset($_GET['id']) && is_numeric($_GET['id'])) 
    {
      Yii::trace('updating favorite');
      $model = new $class;
      $favorite = $model->findByPk($_GET['id']);
      if($favorite)
        $this->updateFavorite($favorite);
    }

    $this->actionFullResponce();
  }

  public function actionAddFeed()
  {
    $this->responce['dialog']['header'] = 'Add Feed';
    if(isset($_POST['feed']))
    {
      $feed=new feed;
      $feed->attributes=$_POST['feed'];
      if($feed->save()) 
        $this->responce['dialog']['content'] = 'Feed Added.  Status: '.$feed->statusText;
      else
      {
        $this->responce['activeFeed-'] = $feed;
        $this->responce['showTab'] = '#feeds';
      }
    }

    $this->actionFullResponce();

  }

  public function actionClearHistory()
  {
    history::model()->deleteAll();
    // no need to pass any variables, the history is now empty
    $this->render('history_dialog');
  }

  public function actionDeleteFeed()
  {
    $this->responce['dialog']['header'] = 'Delete Feed';

    // Verify numeric input, dont allow delete of generic 'All' feeds(with !empty)
    if(!empty($_GET['id']) && is_numeric($_GET['id'])) {
      feed::model()->deleteByPk((integer)$_GET['id']);
      $this->responce['dialog']['content'] = 'Your feed has been successfully deleted';
    }

    $this->actionFullResponce();
  }

  public function actionDlFeedItem()
  {
    $this->responce['dialog']['header'] = 'Download Feed Item';
    
    $feedItem = $this->loadFeedItem();

    if($feedItem)
    {
      if(Yii::app()->dlManager->startDownload($feedItem, feedItem::STATUS_MANUAL_DL))
        $this->responce['dialog']['content'] = $feedItem->title.' has been Started';
      else
      {
        $this->responce['dialog']['error'] = true;
        $this->responce['dialog']['content'] = CHtml::errorSummary(Yii::app()->dlManager);
      }
    } 

    $this->render('dlResponce', array('responce' => $this->responce));
  }

  public function actionFullResponce()
  {
    $app = Yii::app();
    $logger = Yii::getLogger();
    $startTime = microtime(true);
    $config = $app->dvrConfig;
    $time['dvrConfig'] = microtime(true);
    $favoriteMovies = favoriteMovie::model()->with('quality')->findAll();
    $time['favorietMovies'] = microtime(true);
    $favoriteTvShows = favoriteTvShow::model()->with('tvShow', 'quality')->findAll();
    $time['favoriteTvShows'] = microtime(true);
    $favoriteStrings = favoriteString::model()->with('quality')->findAll();
    $time['favoriteStrings'] = microtime(true);
    $feeds = feed::model()->findAll(); // todo: not id 0, which is 'All'
    $time['feeds'] = microtime(true);
    $history = array_reverse(history::model()->findAll());
    $time['history'] = microtime(true);
    $availClients = $app->dlManager->availClients;
    $time['availClients'] = microtime(true);
    $genres = genre::model()->findAll();
    $time['genres'] = microtime(true);


    // get qualitys for use in forms and prepend a blank quality to the list 
    $qualitys = quality::model()->findAll();
    $q = new quality;
    $q->title='';
    $q->id=-1;
    array_unshift($qualitys, $q);
    $time['qualitys'] = microtime(true);

    // Query the various feeditems from the database
    // not AR classes because it takes too much time on the NMT
    $tvEpisodes = $this->prepareFeedItems('tv');
    $time['tvEpisodes'] = microtime(true);
    $movies = $this->prepareFeedItems('movie');
    $time['movies'] = microtime(true);
    $others = $this->prepareFeedItems('other');
    $time['others'] = microtime(true);
    $queued = $this->prepareFeedItems('queued');
    $time['queued'] = microtime(true);

    foreach($time as $key => $value) {
      $time[$key] = $value-$startTime;
      $startTime = $value;
    }

    Yii::log('Database timing '.print_r($time, true), CLogger::LEVEL_PROFILE);
    Yii::log("pre-render: ".$logger->getExecutionTime()."\n", CLogger::LEVEL_PROFILE);
    $this->render('fullResponce', array(
          'availClients'=>$availClients,
          'config'=>$config,
          'favoriteTvShows'=>$favoriteTvShows,
          'favoriteMovies'=>$favoriteMovies,
          'favoriteStrings'=>$favoriteStrings,
          'feeds'=>$feeds,
          'genres'=>$genres,
          'history'=>$history,
          'movies'=>$movies,
          'others'=>$others,
          'qualitys'=>$qualitys,
          'queued'=>$queued,
          'responce'=>$this->responce,
          'tvEpisodes'=>$tvEpisodes,
    ));
    Yii::log("end controller: ".$logger->getExecutionTime()."\n", CLogger::LEVEL_PROFILE);
  }

  public function actionInspect()
  {
    $view = 'inspectError';
    $item = null;
    $opts = array();

    $feedItem = $this->loadFeedItem();
    if($feedItem)
    {
      $opts['item'] = $feedItem;
      $record = $feedItem->itemTypeRecord;
      $view = 'inspect'.ucwords(get_class($record));
      $opts[get_class($record)] = $record;
    }
    $this->render($view, $opts);
  }

  public function actionLoadFeedItems()
  {
    $whiteList = array(
        'tv'=>'TV Episodes', 
        'movie'=>'Movies',
        'other'=>'Others',
        'queued'=>'Queued',
    );
    if(isset($_GET['type']) && in_array($_GET['type'], array_keys($whiteList)))
    {
      $type = $_GET['type'];
      $before = isset($_GET['before']) ? $_GET['before'] : null;
      $items = $this->prepareFeedItems($_GET['type'], $before);
      $this->render('feedItems_container', array(
        $type => $items,
        'tabs' => array(
            $whiteList[$type] => $type,
        ),
      ));
    }
  }

  public function actionResetData()
  {
    $whiteList = array('all', 'media', 'feedItems');
    $this->responce = array('dialog'=>array('header'=>'Reset Data'));

    if(isset($_GET['type']) && in_array($_GET['type'], $whiteList))
    {
      $type = $_GET['type'];
      $transaction = Yii::app()->db->beginTransaction();
      try
      {
        switch($type)
        {
        case 'all':
          foreach(array('feedItem', 'feedItem_quality', 'history', 'movie', 'movie_genre', 'other', 'tvEpisode', 'tvShow') as $class) 
          {
            $model = new $class;
            $class->deleteAll();
          }
          break;
        case 'media':
          movie::model()->updateAll(array('status'=>movie::STATUS_NEW));
          other::model()->updateAll(array('status'=>other::STATUS_NEW));
          tvEpisode::model()->updateAll(array('status'=>tvEpisode::STATUS_NEW));
          break;
        case 'feedItems':
          feedItem::model()->updateAll(array('status'=>feedItem::STATUS_NOMATCH));
          break;
        }
        $transaction->commit();
      } 
      catch (Exception $e)
      {
        $transaction->rollback();
        throw $e;
      }

      if($type === 'all')
      {
        $feeds = feed::model()->findAll();
        foreach($feeds as $feed)
          $feed->updateFeedItems(False);
      }

      Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NOMATCH);
    }
    $this->actionFullResponce();
  }
  public function actionSaveConfig()
  {
    $this->responce = array('dialog'=>array('header'=>'Save Configuration'));

    $config = Yii::app()->dvrConfig;
    Yii::log(print_r($_POST, TRUE));
    if(isset($_POST['category']) && $config->contains($_POST['category']))
    {
      // empty dvrConfig allows still setting config client
      if(isset($_POST['dvrConfigCategory']))
        $config->$_POST['category']->attributes = $_POST['dvrConfigCategory'];

      // if this is a client category, also set the main config to use this client
      if(isset($_POST['type']) && in_array($_POST['type'], array('nzbClient', 'torClient')) &&
         substr($_POST['category'], 0, 6) === 'client')
        $config->$_POST['type'] = $_POST['category'];
    }
    elseif(isset($_POST['dvrConfig']))
    {
      $config->attributes = $_POST['dvrConfig'];
    }

    // Should add some sort of validation in the dvrConfig class
    if($config->save()) 
    {
      $this->responce['dialog']['content'] = 'Configuration successfully saved';
    }
    else
    {
      $this->responce['dialog']['error'] = True;
      $this->responce['dialog']['content'] = 'There was an error saving the configuration';
    }

    $this->actionFullResponce();
  }

  public function actionWizard()
  {
    $this->responce = array('dialog'=>array('header'=>'Initial Configuration', 'content'=>''));

    if(isset($_POST['dvrConfig']))
    {
      $config = Yii::app()->dvrConfig;
      $config->attributes = $_POST['dvrConfig'];
      $this->responce['dialog']['content'] .= ($config->save() ? 'Saved configuration' : 'Failed saving configuration').'<br>';
    }

    if(isset($_POST['feed']))
    {
      $feeds = array();
      foreach(array('torUrl'=>feedItem::TYPE_TORRENT, 'nzbUrl'=>feedItem::TYPE_NZB) as $key => $type)
      {
        if(isset($_POST['feed'][$key]))
        {
          $feed = new feed;
          $feed->url = $_POST['feed'][$key];
          $feed->downloadType = $type;
          $this->responce['dialog']['content'] .= ($feed->save() ? "Saved feed {$feed->title}" : "Failed saving feed {$feed->url}").'<br>';
       }
      }
    }

    if(empty($this->responce['dialog']['content']))
      $this->responce['dialog']['content'] = 'No valid attributes passed to wizard';
    $this->actionFullResponce();
  }

  private function prepareFeedItems($table, $before = null) 
  {
    $table = $table.'FeedItem';
    $db = Yii::app()->db;
    $config = Yii::app()->dvrConfig;

    // First get a listing if the first group of items, and put them in an array indexed by title
    $db->createCommand(
        'CREATE TEMP TABLE prepareItems AS '.
        'SELECT feed_title, feedItem_status, feedItem_description, feedItem_id, feedItem_title, feedItem_pubDate '.
        '  FROM '.$table.
        ($before === null ? '': ' WHERE feedItem_pubDate < '.$before).
        ' LIMIT '.($config->webItemsPerLoad*2)
    )->execute();
    $reader = $db->createCommand('SELECT * FROM prepareItems')->query();
    $items = array();
    foreach($reader as $row) 
    {
      $items[$row['feedItem_title']][] = $row;
    }
    // Then get a listing with a group by clause on the title to get distinct titles, and a count to let us know when
    // to look for extras in the first array
    $sql= 'SELECT count(*) as count, * '.
          '  FROM prepareItems'.
          ' GROUP BY feedItem_title'.
          ' ORDER BY feedItem_pubDate DESC'.
          ' LIMIT '.$config->webItemsPerLoad;
    $reader = $db->createCommand($sql)->query();
    $output = array();
    foreach($reader as $row) 
    {
      if($row['count'] == 1)
        $output[] = $row;
      else {
        // use reference to prevent making aditional copy of array on sort
        $data =& $items[$row['feedItem_title']];
        usort($data, array($this, 'cmpItemStatus'));
        $output[] = $data;
      }
    }
    $db->createCommand('DROP TABLE prepareItems;')->execute();
    return $output;
  }

  public static function cmpItemStatus($a, $b) {
    return($a['feedItem_status'] < $b['feedItem_status']);
  }

  public function actionDeleteFavorite() {
    $this->responce = array('dialog'=>array('header'=>'Delete Favorite'));

    if(isset($_GET['id'], $_GET['type']) && is_numeric($_GET['id']) && in_array($_GET['type'], $this->favoriteWhitelist))
    {
      $id = (integer)$_GET['id'];
      $class = $_GET['type'];
      $model = new $class; // verified safe by whitelist
      Yii::log("deleting $class $id");

      // this logic might be better served in BaseFavorite somehow
      // Have to get the matching information before deleting the row
      // Is casting id to integer enough to make it safe without bindValue?
      $sql = "SELECT feedItem_id FROM matching${class}s WHERE ${class}s_id = $id AND feedItem_status NOT IN".
                  "('".feedItem::STATUS_AUTO_DL."', '".feedItem::STATUS_MANUAL_DL."');";
  
      $reader = Yii::app()->db->CreateCommand($sql)->query();
      $ids = array();
      foreach($reader as $row) 
      {
        $ids[] = $row['feedItem_id'];
      }
   
      if($model->deleteByPk($id))
      {
        // Reset feedItem status on anything this was matching, then rerun matching routine incase something else matches the reset items
        feedItem::model()->updateByPk($ids, array('status'=>feedItem::STATUS_NEW));
        Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NEW);
        $this->responce['dialog']['content'] = 'Your favorite has been successfully deleted';
      } 
      else 
      {
        $this->responce['dialog']['content'] = 'That favorite does not exist ?';
        $this->responce['dialog']['error'] = True;
      }
    }

    $this->actionFullResponce();
  }

}
