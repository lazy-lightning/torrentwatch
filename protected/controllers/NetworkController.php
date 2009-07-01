<?php

class NetworkController extends BaseController
{
  const PAGE_SIZE=10;

  /**
   * @var string specifies the default action to be 'list'.
   */
  public $defaultAction='list';

  /**
   * @var CActiveRecord the currently loaded data model instance.
   */
  private $_network;

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
   * Shows a particular network.
   */
  public function actionShow()
  {
    $this->render('show',array('network'=>$this->loadnetwork()));
  }

  /**
   * Creates a new network.
   * If creation is successful, the browser will be redirected to the 'show' page.
   */
  public function actionCreate()
  {
    $network=new network;
    if(isset($_POST['network']))
    {
      $network->attributes=$_POST['network'];
      if($network->save())
        $this->redirect(array('show','id'=>$network->id));
    }
    $this->render('create',array('network'=>$network));
  }

  /**
   * Updates a particular network.
   * If update is successful, the browser will be redirected to the 'show' page.
   */
  public function actionUpdate()
  {
    $network=$this->loadnetwork();
    if(isset($_POST['network']))
    {
      $network->attributes=$_POST['network'];
      if($network->save())
        $this->redirect(array('show','id'=>$network->id));
    }
    $this->render('update',array('network'=>$network));
  }

  /**
   * Deletes a particular network.
   * If deletion is successful, the browser will be redirected to the 'list' page.
   */
  public function actionDelete()
  {
    if(Yii::app()->request->isPostRequest)
    {
      // we only allow deletion via POST request
      $this->loadnetwork()->delete();
      $this->redirect(array('list'));
    }
    else
      throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
  }

  /**
   * Lists all networks.
   */
  public function actionList()
  {
    $criteria=new CDbCriteria;

    $pages=new CPagination(network::model()->count($criteria));
    $pages->pageSize=self::PAGE_SIZE;
    $pages->applyLimit($criteria);

    $networkList=network::model()->findAll($criteria);

    $this->render('list',array(
      'networkList'=>$networkList,
      'pages'=>$pages,
    ));
  }

  /**
   * Manages all networks.
   */
  public function actionAdmin()
  {
    $this->processAdminCommand();

    $criteria=new CDbCriteria;

    $pages=new CPagination(network::model()->count($criteria));
    $pages->pageSize=self::PAGE_SIZE;
    $pages->applyLimit($criteria);

    $sort=new CSort('network');
    $sort->applyOrder($criteria);

    $networkList=network::model()->findAll($criteria);

    $this->render('admin',array(
      'networkList'=>$networkList,
      'pages'=>$pages,
      'sort'=>$sort,
    ));
  }

  /**
   * Returns the data model based on the primary key given in the GET variable.
   * If the data model is not found, an HTTP exception will be raised.
   * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
   */
  public function loadnetwork($id=null)
  {
    if($this->_network===null)
    {
      if($id!==null || isset($_GET['id']))
        $this->_network=network::model()->findbyPk($id!==null ? $id : $_GET['id']);
      if($this->_network===null)
        throw new CHttpException(500,'The requested network does not exist.');
    }
    return $this->_network;
  }

  /**
   * Executes any command triggered on the admin page.
   */
  protected function processAdminCommand()
  {
    if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
    {
      $this->loadnetwork($_POST['id'])->delete();
      // reload the current page to avoid duplicated delete actions
      $this->refresh();
    }
  }
}
