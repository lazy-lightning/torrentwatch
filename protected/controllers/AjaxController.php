<?php

class AjaxController extends CController
{

  const ERROR_INVALID_ID = "Invalid ID paramater";

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='fullResponce';

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

  // Adds a favorite given a feedItem_id
  public function actionAddFavorite()
  {
    if(isset($_GET['feedItem_id']) && is_numeric($_GET['feedItem_id']))
    {
      $feedItem = feedItem::model()->with('quality')->findByPk($_GET['feedItem_id']);
      if(!empty($feedItem->tvEpisode_id)) 
      {
        $type='favoriteTvShows';
        $fav=new favoriteTvShow;
        $fav->tvShow_id = $feedItem->tvEpisode->tvShow_id;
      } 
      elseif(!empty($feedItem->movie_id)) 
      {
        $type = 'favoriteMovies';
        $fav=new favoriteMovie;
        $fav->genre = $feedItem->movie->genre;
        $fav->name = $feedItem->title;
      } 
      else
      {
        $type = 'favoriteStrings';
        $fav = new favoriteString;
        $fav->filter = $fav->name = $feedItem->title;
      } 
      $fav->feed_id = 0;

      $ids = array();
      foreach($feedItem->quality as $quality) 
      {
        $ids[] = $quality->id;
      }
      $fav->qualityIds = $ids;

      if($fav->save()) 
      {
        $responce['dialog']['content'] = 'New favorite successfully saved';
      }
      else
      {
        $responce['dialog']['error'] = true;
        $responce['dialog']['content'] = 'Failure saving new favorite';
        $responce[$type.'-'] = $fav;
      }
    }
    else
    {
      $responce['dialog']['error'] = true;
      $responce['dialog']['content'] = self::ERROR_INVALID_ID;
    }
    $this->actionFullResponce($responce);
  }

  public function actionUpdateFavorite()
  {
    $responce = array('dialog'=>array('header'=>'Update Favorite'));

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
        $this->deleteFavorite($model, $class);
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
        if($favorite->save() === False)
        {
          $responce['dialog']['content'] = "$class {$favorite->name} successfully saved";
        }
        else
        {
          $responce['dialog']['error'] = true;
          $responce['dialog']['content'] = self::ERROR_INVALID_ID;
          // save with the same format as the id in the view, to be detected and show errors
          // and to bring up the favorites dialog to show them
          $responce[$class.'s-'.$favorite->id] = $favorite;
        }
      }
    }
    catch ( Exception $e )
    {
      $responce['dialog']['error'] = true;
      $responce['dialog']['content'] = $e->error;
    }

    $this->actionFullResponce($responce);
  }

  public function actionAddFeed()
  {
    $responce = array('dialog'=>array('header'=>'Add Feed'));
    $feed=new feed;
    if(isset($_POST['feed']))
    {
      $feed->attributes=$_POST['feed'];
      if($feed->save()) 
      {
        $feed->updateFeedItems();
        $feed->refresh();
        $responce['dialog']['content'] = 'Feed Added.  Status: '.$feed->statusText;
      }
      else
      {
        $responce['dialog']['error'] = true;
        $responce['dialog']['content'] = 'Failure saving new feed';
        $responce['activeFeed-'] = $feed;
      }
    }
    else
    {
      $responce['dialog']['error'] = true;
      $responce['dialog']['content'] = self::ERROR_INVALID_ID;
    }

    $this->actionFullResponce($responce);

  }

  public function actionClearHistory()
  {
    history::model()->deleteAll();
    // no need to pass any variables, the history is now empty
    $this->render('history_dialog');
  }

  public function actionDeleteFeed()
  {
    $responce = array('dialog'=>array('header'=>'Delete Feed'));

    // Verify numeric input, dont allow delete of generic 'All' feeds
    if(isset($_GET['id']) && is_numeric($_GET['id']) &&
       $_GET['id'] != 0) {
      $id = (integer) $_GET['id'];
      if(feed::model()->deleteByPk($id))
      {
        $responce['dialog']['content'] = 'Your feed has been successfully deleted';
      }
      else
      {
        $responce['dialog']['error'] = True;
        $responce['dialog']['content'] = 'There has been a problem deleting your feed.';
      }
    } else {
      $responce['dialog']['error'] = True;
      $responce['dialog']['content'] = self::ERROR_INVALID_ID;
    }

    $this->actionFullResponce($responce);
  }

  public function actionDlFeedItem()
  {
    $responce = array('dialog'=>array('header'=>'Download Feed Item'));
    
    if(isset($_GET['feedItem_id']) && is_numeric($_GET['feedItem_id']))
    {
      $id = (integer)$_GET['feedItem_id'];
      // $feedItem->status gets set by the downloadmanager
      $feedItem=feedItem::model()->findByPk($id);
      if($feedItem === null) 
      {
        $responce['dialog']['error'] = true;
        $responce['dialog']['content'] = 'Unable to load feed item '.$id;
      } 
      elseif(False === Yii::app()->dlManager->startDownload($feedItem, feedItem::STATUS_MANUAL_DL)) 
      {
        $responce['dialog']['error'] = true;
        $responce['dialog']['content'] = 'Failed: '.print_r(Yii::app()->dlManager->getErrors(), true);
      }
    } 
    else
    {
      $responce['dialog']['error'] = true;
      $responce['dialog']['content'] = self::ERROR_INVALID_ID;
    }

    $this->render('dlResponce', array('responce'=>$responce));
  }

  // @param array an errors array to be acted on from any part of the fullResponce view
  public function actionFullResponce($responce = array())
  {
    $app = Yii::app();
    $logger = Yii::getLogger();
    Yii::log("start controller responce: ".$logger->getExecutionTime()."\n", CLogger::LEVEL_ERROR);
    $config = $app->dvrConfig;
    $favoriteMovies = favoriteMovie::model()->findAll();
    $favoriteTvShows = favoriteTvShow::model()->with('tvShow','quality')->findAll();
    $favoriteStrings = favoriteString::model()->findAll();
    $feeds = feed::model()->findAll(); // todo: not id 0, which is 'All'
    $history = history::model()->findAll();
    $availClients = $app->dlManager->availClients;
    $genres = genre::model()->findAll();

    // get qualitys for use in forms and prepend a blank quality to the list 
    $qualitys = quality::model()->findAll();
    $q = new quality;
    $q->title='';
    $q->id=-1;
    array_unshift($qualitys, $q);

    // Query the various feeditems from the database
    // not AR classes because it takes too much time on the NMT
    $group = '';
    if(true) // change to dvrConfig variable
      $group = 'GROUP BY feedItem_title';
    $sql= 'SELECT feedItem_status, feedItem_description, feedItem_id, feedItem_title, feedItem_pubDate from {table} '.$group.' LIMIT '.$config->webItemsPerLoad;
//    $tvEpisodes = $app->db->createCommand(str_replace('{table}', 'tvFeedItem', $sql))->queryAll();
    $tvEpisodes = $this->prepareFeedItems('tvFeedItem');
    $movies = $app->db->createCommand(str_replace('{table}', 'movieFeedItem', $sql))->queryAll();
    $others = $app->db->createCommand(str_replace('{table}', 'otherFeedItem', $sql))->queryAll();

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
          'responce'=>$responce,
          'tvEpisodes'=>$tvEpisodes,
    ));
    Yii::log("end controller: ".$logger->getExecutionTime()."\n", CLogger::LEVEL_ERROR);
  }

  public function actionInspect()
  {
    $view = 'inspectError';
    $item = null;
    if(isset($_GET['feedItem_id']) && is_numeric($_GET['feedItem_id']))
    {
      $item = feedItem::model()->findByPk($_GET['feedItem_id']);
      if($item !== null) {
        if(!empty($item->tvEpisode_id)) 
        {
          $view = 'inspectTvEpisode';
          $opts=array('tvEpisode' => $item->tvEpisode);
        }
        elseif(!empty($item->movie_id))
        {
          $view = 'inspectMovie';
          $opts = array('movie' => $item->movie);
        }
        elseif(!empty($item->other_id)) 
        {
          $view = 'inspectOther';
          $opts = array('other' => $item->other);
        }
      }
    }
    $this->render($view, $opts);
  }

  public function actionSaveConfig()
  {
    $responce = array('dialog'=>array('header'=>'Save Configuration'));

    $config = $index = null;
    Yii::log(print_r($_POST, TRUE), CLogger::LEVEL_ERROR);

    if(isset($_POST['category'], $_POST['type'])) 
    {
      // Saving an individual config category
      // currently only usable for torrent/nzb client subcategorys
      if(in_array($_POST['type'], array('torClient', 'nzbClient'))) 
      {
        $c = Yii::app()->dvrConfig;
        if(isset($_POST['dvrConfigCategory']))
          $index = 'dvrConfigCategory';
        if($c->contains($_POST['category']))
        {
          if(substr($_POST['category'], 0, 6) === 'client') {
            $c->$_POST['type'] = $_POST['category'];
          }
          $config = $c->$_POST['category'];
        }
      }
    }
    elseif(isset($_POST['dvrConfig']))
    {
      $config = Yii::app()->dvrConfig;
      $index = 'dvrConfig';
    }

    // $index === null allows for categorys with no values to still set client type
    if($config !== null && $index !== null) 
    {
      foreach($_POST[$index] as $key => $value) 
      {
        if($config->contains($key))
          $config->$key = $value;
      }
    }
    // Should add some sort of validation in the dvrConfig class
    if(Yii::app()->dvrConfig->save()) 
    {
      $responce['dialog']['content'] = 'Configuration successfully saved';
    }
    else
    {
      $responce['dialog']['error'] = True;
      $responce['dialog']['content'] = 'There was an error saving the configuration';
    }

    $this->actionFullResponce($responce);
  }

  private function prepareFeedItems($table) 
  {
    $db = Yii::app()->db;
    $config = Yii::app()->dvrConfig;

    // First get a listing if the first group of items, and put them in an array indexed by title
    $sql= 'SELECT feedItem_status, feedItem_description, feedItem_id, feedItem_title, feedItem_pubDate '.
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
        // use reference to prevent making aditional copy of array
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
    $id = (integer)$_GET['id'];
    Yii::log("deleting $class $id", CLogger::LEVEL_ERROR);
    // Have to get the matching information before deleting the row
    // Is casting id to integer enough to make it safe without bindValue?
    $sql = "SELECT feedItem_id FROM matching${class}s WHERE ${class}s_id = $id AND feedItem_status NOT IN".
                "('".feedItem::STATUS_AUTO_DL."', '".feedItem::STATUS_MANUAL_DL."');";
    $reader = Yii::app()->db->CreateCommand($sql)->query();
    $ids = array();
    foreach($reader as $row) {
      $ids[] = $row['feedItem_id'];
    }
 
    if($model->deleteByPk($id))
    {
      // Reset feedItem status on anything this was matching, then rerun matching routine incase something else matches the reset items
      feedItem::model()->updateByPk($ids, array('status'=>feedItem::STATUS_NEW));
      Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NEW);
    }
  }

}