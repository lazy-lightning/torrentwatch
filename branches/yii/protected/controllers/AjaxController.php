<?php

class AjaxController extends CController
{

  const ERROR_INVALID_ID = "Invalid ID paramater";

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='fullResponce';

  protected $responce = array();

  /**
   * Initialize the Controller to the ajax layout
   * @return none
   */
  public function init() {
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
/*			array('allow',  // allow all users
				'actions'=>array(),
				'users'=>array('*'),
			), */
			array('allow', // allow authenticated user
				'actions'=>array('fullResponce', 'dlFeedItem', 'saveConfig', 'addFeed', 'addFavorite', 'updateFavorite', 'inspect', 'clearHistory'),
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

    $this->actionFullResponce($this->responce);
  }

  public function actionUpdateFavorite()
  {
    $this->responce = array('dialog'=>array('header'=>'Update Favorite'));

    foreach(array('favoriteTvShow', 'favoriteMovie', 'favoriteString') as $item) 
    {
      if(isset($_POST[$item])) 
      {
        $class = $item;
        break;
      }
    }

    try { 
      if($class === null) 
        throw new CException(self::ERROR_INVALID_ID);

      $model = new $class;

      if(isset($_GET['id'], $_POST['button']) && is_numeric($_GET['id']) && $_POST['button'] === 'Delete') 
      {
        $this->responce = $this->deleteFavorite($model, $class);
      } 
      else 
      {
        if(isset($_GET['id']) && is_numeric($_GET['id'])) 
        {
          Yii::log('updating favorite', CLogger::LEVEL_ERROR);
          $favorite = $model->findByPk($_GET['id']);
          if($favorite === null)
            throw new CException(self::ERROR_INVALID_ID);
        } 
        else 
        {
          Yii::log('creating favorite', CLogger::LEVEL_ERROR);
          $favorite = $model;
        }

        if(isset($_POST['quality_id']))
          $favorite->qualityIds = $_POST['quality_id'];

        $favorite->attributes = $_POST[$class];
        $favorite->save();
        // Tell the view to bring up the changed favorite
        $this->responce[$htmlId] = $favorite;
        $this->responce['showFavorite'] = "#".$class.'s-'.$favorite->id;
        $this->responce['showTab'] = "#".$class."s";
      }
    }
    catch ( Exception $e )
    {
      $this->responce['dialog']['error'] = true;
      $this->responce['dialog']['content'] = $e->error;
    }

    $this->actionFullResponce($this->responce);
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
        $this->responce['dialog']['content'] = $feedItem->fullTitle.' has been Started';
      else
      {
        $this->responce['dialog']['error'] = true;
        $this->responce['dialog']['content'] = 'Failed: '.print_r(Yii::app()->dlManager->getErrors(), true);
      }
    } 

    $this->render('dlResponce', array('responce' => $this->responce));
  }

  // @param array an array of actions to be acted on from any part of the fullResponce view
  public function actionFullResponce()
  {
    $app = Yii::app();
    $logger = Yii::getLogger();
    $startTime = microtime(true);
    $config = $app->dvrConfig;
    $time['dvrConfig'] = microtime(true);
    $favoriteMovies = favoriteMovie::model()->findAll();
    $time['favorietMovies'] = microtime(true);
    $favoriteTvShows = favoriteTvShow::model()->findAll();
    $time['favoriteTvShows'] = microtime(true);
    $favoriteStrings = favoriteString::model()->findAll();
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
    $tvEpisodes = $this->prepareFeedItems('tvFeedItem');
    $time['tvEpisodes'] = microtime(true);
    $movies = $this->prepareFeedItems('movieFeedItem');
    $time['movies'] = microtime(true);
    $others = $this->prepareFeedItems('otherFeedItem');
    $time['others'] = microtime(true);
    $queued = $this->prepareFeedItems('queuedFeedItem');
    $time['queued'] = microtime(true);

    foreach($time as $key => $value) {
      $time[$key] = $value-$startTime;
      $startTime = $value;
    }

    Yii::log('Database timing '.print_r($time, true), CLogger::LEVEL_ERROR);
    Yii::log("pre-render: ".$logger->getExecutionTime()."\n", CLogger::LEVEL_ERROR);
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
    Yii::log("end controller: ".$logger->getExecutionTime()."\n", CLogger::LEVEL_ERROR);
  }

  public function actionInspect()
  {
    $view = 'inspectError';
    $item = null;
    $opts = array();
    if(isset($_GET['feedItem_id']) && is_numeric($_GET['feedItem_id']))
    {
      $item = $opts['item'] = feedItem::model()->findByPk($_GET['feedItem_id']);
      if($item !== null) 
      {
        $record = $item->itemTypeRecord;
        $view = 'inspect'.ucwords(get_class($record));
        $opts[get_class($record)] = $record;
      }
    }
    $this->render($view, $opts);
  }

  public function actionSaveConfig()
  {
    $this->responce = array('dialog'=>array('header'=>'Save Configuration'));

    $config = Yii::app()->dvrConfig;

    Yii::log(print_r($_POST, TRUE), CLogger::LEVEL_ERROR);

    if(isset($_POST['category'], $_POST['type']) && 
       $config->contains($_POST['category']))
    {
      // empty dvrConfig allows still setting config client
      if(isset($_POST['dvrConfigCategory']))
        $config->$_POST['category']->attributes = $_POST['dvrConfigCategory'];

      // if this is a client category, also set the main config to use this client
      if(in_array($_POST['type'], array('nzbClient', 'torClient')) &&
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

    $this->actionFullResponce($this->responce);
  }

  private function prepareFeedItems($table) 
  {
    $db = Yii::app()->db;
    $config = Yii::app()->dvrConfig;

    // First get a listing if the first group of items, and put them in an array indexed by title
    $sql= 'SELECT feed_title, feedItem_status, feedItem_description, feedItem_id, feedItem_title, feedItem_pubDate '.
          '  FROM '.$table.' LIMIT '.($config->webItemsPerLoad*2);
    $reader = $db->createCommand($sql)->query();
    $items = array();
    foreach($reader as $row) 
    {
      $items[$row['feedItem_title']][] = $row;
    }
    // Then get a listing with a group by clause on the title to get distinct titles, and a count to let us know when
    // to look for extras in the first array
    $sql= 'SELECT count(*) as count, * '.
          '  FROM ( SELECT feedItem_status, feedItem_description, feedItem_id, feedItem_title, feedItem_pubDate '.
                 '    FROM '.$table.' LIMIT '.($config->webItemsPerLoad*2).
                 ')'.
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
    return $output;
  }

  public static function cmpItemStatus($a, $b) {
    
    return($a['feedItem_status'] < $b['feedItem_status']);
  }

  // this logic might be better served in a different class
  public function deleteFavorite($model, $class) {
    $this->responce = array('dialog'=>array('header'=>'Delete Favorite'));

    $id = (integer)$_GET['id'];
    Yii::log("deleting $class $id", CLogger::LEVEL_ERROR);

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

}
