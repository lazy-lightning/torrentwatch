<?php

class FeedController extends BaseController
{
  // TODO: some thought should be given to this
  const PAGE_SIZE=100;

  /**
   * @var string specifies the default action to be 'list'.
   */
  public $defaultAction='list';

  /**
   * @var CActiveRecord the currently loaded data model instance.
   */
  private $_feed;

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
      array('allow', // allow authenticated user
        'actions'=>array('create','update', 'list', 'show', 'delete'),
        'users'=>array('@'),
      ),
      array('allow', // allow admin user to perform 'admin' actions
        'actions'=>array('admin'),
        'users'=>array('admin'),
      ),
      array('deny',  // deny all users
        'users'=>array('*'),
      ),
    );
  }

  /**
   * Shows a particular feed.
   */
  public function actionShow()
  {
    $this->render('show',array(
        'model'=>$this->loadfeed(),
        'response'=>Yii::app()->getUser()->getFlash('response'),
    ));
  }

  /**
   * Creates a new feed.
   * The browser will be redirected to ajax/fullResponse
   */
  public function actionCreate()
  {
    $feed=new feed;
    if(isset($_POST['feed']))
    {
      $response = array('dialog'=>array('header'=>'Create Feed'));
      $transaction = $feed->getDbConnection()->beginTransaction();
      try {
        $feed->attributes=$_POST['feed'];
        $success = $feed->save();
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
      if($success) {
        Yii::app()->getUser()->setFlash('response', array('resetFeedItems'=>true));
        $this->redirect(array('list'));
      }
    }
    $this->render('update',array('model'=>$feed));
  }

  /**
   * Updates a particular feed.
   * If update is successful, the browser will be redirected to the 'list' page.
   */
  public function actionUpdate()
  {
    $feed=$this->loadfeed();
    $success = false;
    if(isset($_POST['feed']))
    {
      $transaction = $feed->getDbConnection()->beginTransaction();
      try {
        $feed->attributes=$_POST['feed'];
        $success = $feed->save();
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
      if($success) {
        Yii::app()->getUser()->setFlash('response', array('resetFeedItems'=>true));
        $this->redirect(array('list'));
      }
    }
    $this->render('update',array('model'=>$feed));
  }

  /**
   * Deletes a particular feed.
   * If deletion is successful, the browser will be redirected to the 'list' page.
   */
  public function actionDelete()
  {
    $feed = $this->loadfeed();
    $transaction = $feed->getDbConnection()->beginTransaction();
    try {
      $feed->delete();
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
    Yii::app()->getUser()->setFlash('response', array('resetFeedItems'=>true));
    $this->redirect(array('list'));
  }

  /**
   * Lists all feeds.
   */
  public function actionList()
  {
    $criteria=new CDbCriteria;

    $pages=new CPagination(feed::model()->count($criteria));
    $pages->pageSize=self::PAGE_SIZE;
    $pages->applyLimit($criteria);

    $feedList=feed::model()->findAll($criteria);
    $this->render('list',array(
      'feedList'=>$feedList,
      'pages'=>$pages,
      'response'=>Yii::app()->getUser()->getFlash('response'),
    ));
  }

  /**
   * Manages all feeds.
   */
  public function actionAdmin()
  {
    $this->processAdminCommand();

    $criteria=new CDbCriteria;

    $pages=new CPagination(feed::model()->count($criteria));
    $pages->pageSize=self::PAGE_SIZE;
    $pages->applyLimit($criteria);

    $sort=new CSort('feed');
    $sort->applyOrder($criteria);

    $feedList=feed::model()->findAll($criteria);

    $this->render('admin',array(
        'feedList'=>$feedList,
        'pages'=>$pages,
        'sort'=>$sort,
    ));
  }

  /**
   * Returns the data model based on the primary key given in the GET variable.
   * If the data model is not found, an HTTP exception will be raised.
   * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
   */
  public function loadfeed($id=null)
  {
    if($this->_feed===null)
    {
      if($id!==null || isset($_GET['id']))
        $this->_feed=feed::model()->findbyPk($id!==null ? $id : $_GET['id']);
      if($this->_feed===null)
        throw new CHttpException(500,'The requested feed does not exist.');
    }
    return $this->_feed;
  }

  /**
   * Executes any command triggered on the admin page.
   */
  protected function processAdminCommand()
  {
    if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
    {
      $transaction = Yii::app()->db->beginTransaction();
      try {
        $this->loadfeed($_POST['id'])->delete();
        $transaction->commit();
      } 
      catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
      // reload the current page to avoid duplicated delete actions
      $this->refresh();
    }

    if (isset($_GET['command']) && $_GET['command']==='updateFeedItems')
    {
      // NOTE: transaction is handled inside updateFeedItems due to network
      //       access and lock length
      if(isset($_GET['id'])) {
        Yii::log('performing single update');
        $this->loadfeed($_GET['id'])->updateFeedItems();
        Yii::log('update complete');
      } else {
        foreach(feed::model()->findAll() as $feed)
          $feed->updateFeedItems();
      }
    }
  }
}
