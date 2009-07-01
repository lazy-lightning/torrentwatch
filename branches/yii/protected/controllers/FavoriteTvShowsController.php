<?php

class FavoriteTvShowsController extends BaseController
{
  const PAGE_SIZE=10;

  /**
   * @var string specifies the default action to be 'list'.
   */
  public $defaultAction='list';

  /**
   * @var CActiveRecord the currently loaded data model instance.
   */
  private $_favorite;

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
   * Shows a particular favorite.
   */
  public function actionShow()
  {
    $this->render('show',array('favorite'=>$this->loadfavorite()));
  }

  /**
   * Creates a new favorite.
   * If creation is successful, the browser will be redirected to the 'show' page.
   */
  public function actionCreate()
  {
    $favorite=new favoriteTvShow;
    if(isset($_POST['favorite']))
    {
      $favorite->attributes=$_POST['favoriteTvShow'];
      if($favorite->save())
        $this->redirect(array('show','id'=>$favorite->id));
    } elseif(isset($_GET['feedItem_id'])) {
      $x = True;
      $feedItem = feedItem::model()->with('tvEpisode')->findByPk($_GET['feedItem_id']);
      $favorite = new favoriteTvShow;
      $favorite->tvShow_id = $feedItem->tvEpisode->tvShow_id;
      if($favorite->save()) {
        foreach($feedItem->quality as $q) {
          $record = new favoriteTvShow_quality;
          $record->tvShow_id = $favorite->id;
          $record->quality_id = factory::qualityByTitle($q);
          $record->save();
        }
        $this->redirect(array('show', 'id'=>$favorite->id));
      }
    }
    $this->render('create',array('favorite'=>$favorite));
  }

  /**
   * Updates a particular favorite.
   * If update is successful, the browser will be redirected to the 'show' page.
   */
  public function actionUpdate()
  {
    $favorite=$this->loadfavorite();
    if(isset($_POST['favorite']))
    {
      $favorite->attributes=$_POST['favoriteTvShow'];
      if($favorite->save())
        $this->redirect(array('show','id'=>$favorite->id));
    }
    $this->render('update',array('favorite'=>$favorite));
  }

  /**
   * Deletes a particular favorite.
   * If deletion is successful, the browser will be redirected to the 'list' page.
   */
  public function actionDelete()
  {
    if(Yii::app()->request->isPostRequest)
    {
      // we only allow deletion via POST request
      $this->loadfavorite()->delete();
      $this->redirect(array('list'));
    }
    else
      throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
  }

  /**
   * Lists all favorites.
   */
  public function actionList()
  {
    $criteria=new CDbCriteria;

    $pages=new CPagination(favoriteTvShow::model()->count($criteria));
    $pages->pageSize=self::PAGE_SIZE;
    $pages->applyLimit($criteria);

    $favoriteList=favoriteTvShow::model()->findAll($criteria);

    $this->render('list',array(
      'favoriteList'=>$favoriteList,
      'pages'=>$pages,
    ));
  }

  /**
   * Manages all favorites.
   */
  public function actionAdmin()
  {
    $this->processAdminCommand();

    $criteria=new CDbCriteria;

    $pages=new CPagination(favoriteTvShow::model()->count($criteria));
    $pages->pageSize=self::PAGE_SIZE;
    $pages->applyLimit($criteria);

    $sort=new CSort('favoriteTvShow');
    $sort->applyOrder($criteria);

    $favoriteList=favoriteTvShow::model()->findAll($criteria);

    $this->render('admin',array(
      'favoriteList'=>$favoriteList,
      'pages'=>$pages,
      'sort'=>$sort,
    ));
  }

  /**
   * Returns the data model based on the primary key given in the GET variable.
   * If the data model is not found, an HTTP exception will be raised.
   * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
   */
  public function loadfavorite($id=null)
  {
    if($this->_favorite===null)
    {
      if($id!==null || isset($_GET['id']))
        $this->_favorite=favoriteTvShow::model()->findbyPk($id!==null ? $id : $_GET['id']);
      if($this->_favorite===null)
        throw new CHttpException(500,'The requested favorite does not exist.');
    }
    return $this->_favorite;
  }

  /**
   * Executes any command triggered on the admin page.
   */
  protected function processAdminCommand()
  {
    if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
    {
      $this->loadfavorite($_POST['id'])->delete();
      // reload the current page to avoid duplicated delete actions
      $this->refresh();
    }
  }
}
