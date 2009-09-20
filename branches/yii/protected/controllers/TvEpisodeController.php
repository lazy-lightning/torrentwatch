<?php

class TvEpisodeController extends BaseController
{
  const PAGE_SIZE=10;

  /**
   * @var string specifies the default action to be 'list'.
   */
  public $defaultAction='list';

  /**
   * @var CActiveRecord the currently loaded data model instance.
   */
  private $_tvepisode;

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
      array('allow',  // allow all users to perform 'list' and 'show' actions
        'actions'=>array('list','show'),
        'users'=>array('*'),
      ),
      array('allow', // allow authenticated user to perform 'create' and 'update' actions
        'actions'=>array('create','update'),
        'users'=>array('@'),
      ),
      array('allow', // allow admin user to perform 'admin' and 'delete' actions
        'actions'=>array('admin','delete'),
        'users'=>array('admin'),
      ),
      array('deny',  // deny all users
        'users'=>array('*'),
      ),
    );
  }

  /**
   * Shows a particular tvepisode.
   */
  public function actionShow()
  {
    $this->render('show',array('tvepisode'=>$this->loadtvEpisode()));
  }

  /**
   * Creates a new tvepisode.
   * If creation is successful, the browser will be redirected to the 'show' page.
   */
  public function actionCreate()
  {
    $tvepisode=new tvEpisode;
    if(isset($_POST['tvEpisode']))
    {
      $tvepisode->attributes=$_POST['tvEpisode'];
      if($tvepisode->save())
        $this->redirect(array('show','id'=>$tvepisode->id));
    }
    $this->render('create',array('tvepisode'=>$tvepisode));
  }

  /**
   * Updates a particular tvepisode.
   * If update is successful, the browser will be redirected to the 'show' page.
   */
  public function actionUpdate()
  {
    $tvepisode=$this->loadtvEpisode();
    if(isset($_POST['tvEpisode']))
    {
      $tvepisode->attributes=$_POST['tvEpisode'];
      if($tvepisode->save())
        $this->redirect(array('show','id'=>$tvepisode->id));
    }
    $this->render('update',array('tvepisode'=>$tvepisode));
  }

  /**
   * Deletes a particular tvepisode.
   * If deletion is successful, the browser will be redirected to the 'list' page.
   */
  public function actionDelete()
  {
    if(Yii::app()->request->isPostRequest)
    {
      // we only allow deletion via POST request
      $this->loadtvEpisode()->delete();
      $this->redirect(array('list'));
    }
    else
      throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
  }

  /**
   * Lists all tvepisodes.
   */
  public function actionList()
  {
    $pages = null;
    $criteria=new CDbCriteria(array(
          'select'=>'id, status, title, season, episode', 
          'order'=>'tvEpisode.lastUpdated DESC',
    )) ;

    if(isset($_GET['tvShow']))
    {
      $criteria->condition = 'tvEpisode.tvShow_id = :tvShow_id';
      $criteria->params = array(':tvShow_id'=>$_GET['tvShow']);
    }

    $pages=new CPagination(tvEpisode::model()->count($criteria));
    $pages->pageSize=Yii::app()->dvrConfig->webItemsPerLoad;
    $pages->applyLimit($criteria);

    $tvepisodeList=tvEpisode::model()->with(array(
          'tvShow'=>array(
            'select'=>'id,title',
          ),
    ))->findAll($criteria);

    $this->render('list',array(
      'tvepisodeList'=>$tvepisodeList,
      'pages'=>$pages,
    ));
  }

  /**
   * Manages all tvepisodes.
   */
  public function actionAdmin()
  {
    $this->processAdminCommand();

    $criteria=new CDbCriteria;

    $pages=new CPagination(tvEpisode::model()->count($criteria));
    $pages->pageSize=self::PAGE_SIZE;
    $pages->applyLimit($criteria);

    $sort=new CSort('tvEpisode');
    $sort->applyOrder($criteria);

    $tvepisodeList=tvEpisode::model()->findAll($criteria);

    $this->render('admin',array(
      'tvepisodeList'=>$tvepisodeList,
      'pages'=>$pages,
      'sort'=>$sort,
    ));
  }

  /**
   * Returns the data model based on the primary key given in the GET variable.
   * If the data model is not found, an HTTP exception will be raised.
   * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
   */
  public function loadtvEpisode($id=null)
  {
    if($this->_tvepisode===null)
    {
      if($id!==null || isset($_GET['id']))
        $this->_tvepisode=tvEpisode::model()->findbyPk($id!==null ? $id : $_GET['id']);
      if($this->_tvepisode===null)
        throw new CHttpException(500,'The requested tvepisode does not exist.');
    }
    return $this->_tvepisode;
  }

  /**
   * Executes any command triggered on the admin page.
   */
  protected function processAdminCommand()
  {
    if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
    {
      $this->loadtvEpisode($_POST['id'])->delete();
      // reload the current page to avoid duplicated delete actions
      $this->refresh();
    }
  }
}
