<?php

class AjaxController extends CController
{
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
				'actions'=>array('fullResponce', 'dlFeedItem', 'saveConfig', 'addFeed', 'addFavorite', 'updateFavorite'),
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
      if(!empty($feedItem->tvEpisode_id)) {
        $fav=new favoriteTvShow;
        $fav->tvShow_id = $feedItem->tvEpisode->tvShow_id;
      } elseif(!empty($feedItem->movie_id)) {
        $fav=new favoriteMovies;
        $fav->genre = $feedItem->movie->genre;
        $fav->name = $feedItem->title;
      } elseif(!empty($feedItem->other_id)) {
        $fav = new favoriteStrings;
        $fav->filter = $feedItem->title;
        $fav->name = $feedItem->title;
      }
      $fav->feed_id = 0;

      $ids = array();
      foreach($feedItem->quality as $quality) {
        $ids[] = $quality->id;
      }
      $fav->qualityIds = $ids;

      if($fav->save()) {
      }
    }
    // should have another else to direct to an error, also if ->save() fails
    $this->redirect(array('fullResponce'));
  }

  public function actionUpdateFavorite()
  {
    foreach(array('favoriteTvShow', 'favoriteMovie', 'favoriteOther') as $item) 
    {
      if(isset($_POST[$item])) 
      {
        $class = $item;
        break;
      }
    }

    if($class !== null) 
    {
      $model = new $class;

      if(isset($_GET['id'], $_POST['button']) && is_numeric($_GET['id']) && $_POST['button'] === 'Delete') 
      {
        $this->deleteFavorite($model, $class);
      } 
      else 
      {
        if(isset($_GET['id'])) 
        {
          Yii::log('updating favorite', CLogger::LEVEL_ERROR);
          $favorite = $model->findByPk($_GET['id']);
          if($favorite === null)
            throw new CException("Unable to load $class: bad id ".$_GET['id']);
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
      }
    }
    $this->redirect(array('fullResponce'));
  }

  public function actionAddFeed()
  {
    $feed=new feed;
    if(isset($_POST['feed']))
    {
      $feed->attributes=$_POST['feed'];
      if($feed->save())
        $feed->updateFeedItems();
    }
    $this->redirect(array('fullResponce'));
  }

  public function actionDeleteFeed()
  {
    // Verify numeric input, dont allow delete of generic 'All' feeds
    if(isset($_GET['id']) && is_numeric($_GET['id']) &&
       $_GET['id'] != 0) {
      $id = (integer) $_GET['id'];
      if(feed::model()->deleteByPk($id))
      {
        // Clean out related feed items
        feedItem::model()->deleteAll('feed_id = :feed_id', array(':feed_id'=>$id));
        // Update related favorites to generic 'All' feeds
        favoriteTvShow::model()->updateAll(array('feed_id'=>0), "feed_id = $id");
      }
    }
    $this->redirect(array('fullResponce'));
  }

  public function actionDlFeedItem()
  {
    $error = False;
    if(isset($_GET['id']))
    {
      // $feedItem->status gets set by the downloadmanager
      $feedItem=feedItem::model()->findByPk($_GET['id']);
      if($feedItem === null) 
      {
        $error = 'Unable to load feed item '.$_GET['id'];
      } 
      elseif(False === Yii::app()->dlManager->startDownload($feedItem, feedItem::STATUS_MANUAL_DL)) 
      {
        $error = 'Failed: '.Yii::app()->dlManager->getErrors();
      }
    } else
      $error = 'No id given';

    $this->render('dlResponce', array('error'=>$error));
  }

  public function actionFullResponce()
  {
    $app = Yii::app();
    $logger = Yii::getLogger();
    Yii::log("start controller responce: ".$logger->getExecutionTime()."\n", CLogger::LEVEL_ERROR);
    $config = $app->dvrConfig;
    $favoriteMovies = favoriteMovies::model()->findAll();
    $favoriteTvShows = favoriteTvShow::model()->with('tvShow','quality')->findAll();
    $feeds = feed::model()->findAll(); // todo: not id 0, which is 'All'
    $availClients = $app->dlManager->availClients;
    $genres = genre::model()->findAll();
    $qualitys = quality::model()->findAll();
    // prepend a blank quality to the list 
    $q = new quality;
    $q->title='';
    $q->id=-1;
    array_unshift($qualitys, $q);
    

    $tvEpisodes = $app->db->createCommand(
        'SELECT feedItem_status, feedItem_description, feedItem_id, feedItem_title, feedItem_pubDate from tvFeedItem LIMIT '.$config->webItemsPerLoad
    )->queryAll(); 
    $movies = $app->db->createCommand(
        'SELECT feedItem_status, feedItem_description, feedItem_id, feedItem_title, feedItem_pubDate from movieFeedItem LIMIT '.$config->webItemsPerLoad
    )->queryAll(); 
    $others = $app->db->createCommand(
        'SELECT feedItem_status, feedItem_description, feedItem_id, feedItem_title, feedItem_pubDate from otherFeedItem LIMIT '.$config->webItemsPerLoad
    )->queryAll();

    Yii::log("pre-render: ".$logger->getExecutionTime()."\n", CLogger::LEVEL_ERROR);
    $this->render('fullResponce', array(
          'availClients'=>$availClients,
          'config'=>$config,
          'favoriteTvShows'=>$favoriteTvShows,
          'favoriteMovies'=>$favoriteMovies,
          'feeds'=>$feeds,
          'genres'=>$genres,
          'movies'=>$movies,
          'others'=>$others,
          'qualitys'=>$qualitys,
          'tvEpisodes'=>$tvEpisodes,
    ));
    Yii::log("post-render: ".$logger->getExecutionTime()."\n", CLogger::LEVEL_ERROR);
  }

  public function actionSaveConfig()
  {
    $index = 'dvrConfig';
    $config = null;
    if(isset($_POST['dvrConfigCategory'], $_POST['category'], $_POST['type'])) 
    {
      if(in_array($_POST['type'], array('torClient', 'nzbClient'))) 
      {
        $c = Yii::app()->dvrConfig;
        $index = 'dvrConfigCategory';
        if($c->contains($_POST['category']))
        {
          $c->$_POST['type'] = $_POST['category'];
          $config = $c->$_POST['category'];
        }
      }
    }
    elseif(isset($_POST['dvrConfig']))
    {
      $config = Yii::app()->dvrConfig;
    }

    if($config !== null) 
    {
      foreach($_POST[$index] as $key => $value) 
      {
        if($config->contains($key))
          $config->$key = $value;
      }
      // Should add some sort of validation in the dvrConfig class
      Yii::app()->dvrConfig->save();
    }
    $this->redirect(array('fullResponce'));
  }

  // the core of this logic might be better served in a different class
  public function deleteFavorite($model, $class) {
    $id = (integer)$_GET['id'];
    Yii::log("deleting $class $id", CLogger::LEVEL_ERROR);
    // Have to get the matching information before deleting the row
    // Is casting id to integer enough to make it safe without bindValue?
    $reader = Yii::app()->db->CreateCommand("SELECT feedItem_id FROM matching${class}s WHERE ${class}s_id = $id AND feedItem_status NOT IN".
                                                    "('".feedItem::STATUS_AUTO_DL."', '".feedItem::STATUS_MANUAL_DL."');")->query();
 
    if($model->deleteByPk($id))
    {
      // Delete related many_many relationship
      favoriteTvShow_quality::deleteByAttributes('favoriteTvShow_id=:id', array(':id'=>$id));
      // Reset feedItem status on anything this was matching, then rerun matching routine incase something else matches the reset items
      $ids = array();
      foreach($reader as $row) {
        $ids[] = $row['feedItem_id'];
      }
      feedItem::model()->updateByPk($ids, array('status'=>feedItem::STATUS_NEW));
      Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NEW);
    }
  }

}
