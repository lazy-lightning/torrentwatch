<?php

class AjaxController extends BaseController
{

  const ERROR_INVALID_ID = "Invalid ID paramater";

  /**
   * @var string specifies the default action to be 'list'.
   */
  public $defaultAction='fullResponse';

  /**
   * @var array response data to be passed to the view
   */
  protected $response = array();

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
        'actions'=>array('fullResponse'),
        'users'=>array('*'),
      ), */
      array('allow', // allow authenticated user
        'actions'=>array(
            'fullResponse',
            'inspect', 'resetData',
            'hideShow', 'unHideTvShow',
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

    $this->response['dialog']['error'] = true;
    $this->response['dialog']['content'] = self::ERROR_INVALID_ID;
    return false;
  }

  public function actionFullResponse()
  {
    $app = Yii::app();
    $logger = Yii::getLogger();
    $startTime = microtime(true);
    $config = $app->dvrConfig;
    $time['dvrConfig'] = microtime(true);
    $favoriteMovies = favoriteMovie::model()->findAll(array('select'=>'id,name'));
    $time['favoriteMovies'] = microtime(true);
    $favoriteTvShows = favoriteTvShow::model()->with(array('tvShow'=>array('select'=>'id,title')))->findAll(array('select'=>'id'));
    $time['favoriteTvShows'] = microtime(true);
    $favoriteStrings = favoriteString::model()->findAll(array('select'=>'id,name'));
    $time['favoriteStrings'] = microtime(true);
    $feeds = feed::model()->findAll(); // todo: not id 0, which is 'All'
    $time['feeds'] = microtime(true);
    $availClients = $app->dlManager->availClients;
    $time['availClients'] = microtime(true);

    foreach($time as $key => $value) {
      $time[$key] = $value-$startTime;
      $startTime = $value;
    }

    // Response was set before a redirect to this action occured
    if(isset($_GET['response'])) {
      $this->response = Yii::app()->user->getFlash('response');
    }

    Yii::log('Database timing '.print_r($time, true), CLogger::LEVEL_PROFILE);
    Yii::log("pre-render: ".$logger->getExecutionTime()."\n", CLogger::LEVEL_PROFILE);
    $this->render('fullResponse', array(
          'availClients'=>$availClients,
          'config'=>$config,
          'favoriteTvShows'=>$favoriteTvShows,
          'favoriteMovies'=>$favoriteMovies,
          'favoriteStrings'=>$favoriteStrings,
          'feeds'=>$feeds,
          'response'=>$this->response,
    ));
    Yii::log("end controller: ".$logger->getExecutionTime()."\n", CLogger::LEVEL_PROFILE);
  }

  public function actionHideTvShow()
  {
    $this->response['dialog']['header'] = 'Hide Tv Show';
    $feedItem = $this->loadFeedItem();
    if($feedItem && $feedItem->tvShow_id)
    {
      if(favoriteTvShow::model()->exists('tvShow_id = '.$feedItem->tvShow_id))
      {
        $this->respose['dialog']['content'] = 'This show cannot be hidden as it is favorited.';
      }
      else
      {
        $show = $feedItem->tvShow;
        try {
          $transaction = $feedItem->dbConnection->beginTransaction();
          $show->hide = true;
          $show->save();
          $transaction->commit();
          $this->response['dialog']['content'] = $show->title.' will no longer be displayed.';
        } catch (Exception $e) {
          $transaction->rollback();
          throw $e;
        }
      }
    }

    $this->actionFullResponse();
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

  public function actionResetData()
  {
    $whiteList = array('all', 'media', 'feedItems');
    $this->response = array('dialog'=>array('header'=>'Reset Data'));

    if(isset($_GET['type']) && in_array($_GET['type'], $whiteList))
    {
      $type = $_GET['type'];
      try
      {
        $transaction = Yii::app()->db->beginTransaction();
        switch($type)
        {
        case 'all':
          foreach(array('feedItem', 'feedItem_quality', 'history', 'movie', 'movie_genre', 'other', 'tvEpisode') as $class) 
          {
            $model = new $class;
            $model->deleteAll();
          }
          tvShow::model()->deleteAll('id NOT IN (SELECT tvShow_id from favoriteTvShows)');
          break;
        case 'media':
          movie::model()->updateAll(array('status'=>movie::STATUS_NEW));
          other::model()->updateAll(array('status'=>other::STATUS_NEW));
          tvEpisode::model()->updateAll(array('status'=>tvEpisode::STATUS_NEW));
          break;
        case 'feedItems':
          feedItem::model()->updateAll(array('status'=>feedItem::STATUS_NEW));
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

      Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NEW);
      $this->response['dialog']['content'] = 'Reset has been successfull';
    }
    $this->actionFullResponse();
  }

  public function actionUnHideTvShow()
  {
    if(isset($_GET['tvShow_id']) && is_array($_GET['tvShow_id']))
    {
      try {
        Yii::app()->db->beginTransaction();
        $shows = tvShow::model()->findAllByPk($_GET['tvShow_id']);
        foreach($shows as $s)
        {
          $s->hide = false;
          $s->save();
        }
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
    }
    $this->actionFullResponse();
  }
}
