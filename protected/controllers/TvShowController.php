<?php

class TvShowController extends BaseController
{
  const PAGE_SIZE=10;

  /**
   * @var string specifies the default action to be 'list'.
   */
  public $defaultAction='list';

  /**
   * @var CActiveRecord the currently loaded data model instance.
   */
  private $_tvshow;

  public function actions()
  {
    return array(
      'makeFavorite'=>'makeFavoriteAction',
    );
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
      array('allow', // allow authenticated user to perform actions
        'actions'=>array('list', 'show', 'hide', 'makeFavorite'),
        'users'=>array('@'),
      ),
      array('deny',  // deny all users
        'users'=>array('*'),
      ),
    );
  }

  /**
   * Shows a particular tvshow.
   */
  public function actionShow()
  {
    $this->render('show',array('tvshow'=>$this->loadtvShow()));
  }

  /**
   * Lists all tvshows.
   */
  public function actionList()
  {
    $criteria=new CDbCriteria;

    $pages=new CPagination(tvShow::model()->count($criteria));
    $pages->pageSize=Yii::app()->dvrConfig->webItemsPerLoad;
    $pages->applyLimit($criteria);

//    $tvshowList=tvShow::model()->findAll($criteria);
    $tvshowList=tvShow::model()->findAllBySql('SELECT * FROM recentTvShows LIMIT :limit OFFSET :offset',
        array(':offset'=>$pages->pageSize*$pages->currentPage,':limit'=>$pages->pageSize));

    $this->render('list',array(
      'tvshowList'=>$tvshowList,
      'pages'=>$pages,
    ));
  }

  public function actionHide()
  {
    $id = $_GET['id'];
    if(favoriteTvShow::model()->exists('tvShow_id = :id', array(':id'=>$id)))
    {
      $this->widget('actionResponseWidget', array(
            'dialog'=>array(
                'heading'=>'Hide Tv Show',
                'content'=>'This tv show will not be hidden as it is currently favorited.',
            ),
      ));
    }
    else
    {
      $model = $this->loadtvShow($id);
      $transaction = $model->getDbConnection()->beginTransaction();
      try {
        $model->hide = true;
        $model->save();
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
      // TODO: Somehow the choice between tvEpisode and tvShow controllers
      // should be programatic.
      $this->redirect(array('/tvEpisode/list'));
    }
  }

  /**
   * Returns the data model based on the primary key given in the GET variable.
   * If the data model is not found, an HTTP exception will be raised.
   * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
   */
  public function loadtvShow($id=null)
  {
    if($this->_tvshow===null)
    {
      if($id!==null || isset($_GET['id']))
        $this->_tvshow=tvShow::model()->findbyPk($id!==null ? $id : $_GET['id']);
      if($this->_tvshow===null)
        throw new CHttpException(500,'The requested tvshow does not exist.');
    }
    return $this->_tvshow;
  }
}
