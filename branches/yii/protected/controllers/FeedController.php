<?php

class FeedController extends BaseController
{
  // TODO: some thought should be given to this
  const PAGE_SIZE=10;

  /**
   * @var string specifies the default action to be 'list'.
   */
  public $defaultAction='list';

  /**
   * @var CActiveRecord the currently loaded data model instance.
   */
  private $_feed;

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
      $success = $this->applyAttributes($feed, $_POST['feed'], array('downloadType', 'url', 'userTitle'));
      if($feed->status === feed::STATUS_ERROR) 
      {
        $feed->addError('url', $feed->getStatusText());
        $this->deleteModel($feed);
      }
      else if ($success)
      {
        Yii::app()->getUser()->setFlash('response', array('resetFeedItems'=>true));
        $this->redirect(array('list'));
      }
    }
    $this->render('create',array('model'=>$feed));
  }

  /**
   * Updates a particular feed.
   * If update is successful, the browser will be redirected to the 'list' page.
   */
  public function actionUpdate()
  {
    $feed=$this->loadfeed();
    $response = array();
    $success = false;
    if(isset($_POST['feed']))
    {
      $oldAttr = $feed->getAttributes(array('downloadType', 'status', 'url', 'userTitle'));
      $success = $this->applyAttributes($feed, $_POST['feed'], array('downloadType', 'url', 'userTitle'));
      if($oldAttr['url'] != $feed->url)
      {
        $feed->updateFeedItems();
        if($feed->status === feed::STATUS_ERROR)
        {
          // use a clone to revert settings so DB stays the same as before action
          // but without affecting the model we need to put back in the form.
          // could have used a transaction and rolled back, but then holding
          // transaction during network access.
          $this->applyAttributes(clone $feed, $oldAttr);
          $feed->addError('url', $feed->getStatusText());
          $success = false;
        }
        else
        {
          $response['resetFeedItems'] = true;
        }
      }
    }
    $this->render('update',array(
          'model'=>$feed,
          'respose'=>$response,
          'success'=>$success,
    ));
  }

  /**
   * Deletes a particular feed.
   * If deletion is successful, the browser will be redirected to the 'list' page.
   */
  public function actionDelete()
  {
    $this->deleteModel($this->loadfeed());

    Yii::app()->getUser()->setFlash('response', array('resetFeedItems'=>true));
    $this->redirect(array('list'));
  }

  /**
   * Lists all feeds.
   */
  public function actionList()
  {
    $criteria=new CDbCriteria;
    $pages=false;

    if(isset($_GET['id']))
      $feedList = array($this->loadfeed());
    else
    {
      if(false === Yii::app()->request->isAjaxRequest)
      {
        $pages=new CPagination(feed::model()->count($criteria));
        $pages->pageSize=self::PAGE_SIZE;
        $pages->applyLimit($criteria);
      }
  
      $feedList=feed::model()->findAll($criteria);
    }
    $this->render('list',array(
      'feedList'=>$feedList,
      'fullList'=>!isset($_GET['id']),
      'pages'=>$pages,
      'response'=>Yii::app()->getUser()->getFlash('response'),
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

}
