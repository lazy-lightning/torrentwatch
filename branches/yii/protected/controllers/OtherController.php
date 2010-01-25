<?php

class OtherController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_other;

  public function actions()
  {
    return array(
        'startDownload' => 'startDownloadAction',
        'makeFavorite' => 'makeFavoriteAction',
        'hide' => 'hideRelatedAction',
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
	 * Shows a particular other.
	 */
	public function actionShow()
	{
		$this->render('show',array('other'=>$this->loadother()));
	}

	/**
	 * Creates a new other.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$other=new other;
		if(isset($_POST['other']))
		{
			$other->attributes=$_POST['other'];
			if($other->save())
				$this->redirect(array('show','id'=>$other->id));
		}
		$this->render('create',array('other'=>$other));
	}

	/**
	 * Updates a particular other.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$other=$this->loadother();
		if(isset($_POST['other']))
		{
			$other->attributes=$_POST['other'];
			if($other->save())
				$this->redirect(array('show','id'=>$other->id));
		}
		$this->render('update',array('other'=>$other));
	}

	/**
	 * Deletes a particular other.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadother()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all others.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria(array('order'=>'lastUpdated DESC'));
    $pages = null;

		$pages=new CPagination(other::model()->count($criteria));
   	$pages->pageSize=Yii::app()->dvrConfig->webItemsPerLoad;
		$pages->applyLimit($criteria);

		$otherList=other::model()->findAll($criteria);

		$this->render('list',array(
			'otherList'=>$otherList,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all others.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$criteria=new CDbCriteria;

		$pages=new CPagination(other::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$sort=new CSort('other');
		$sort->applyOrder($criteria);

		$otherList=other::model()->findAll($criteria);

		$this->render('admin',array(
			'otherList'=>$otherList,
			'pages'=>$pages,
			'sort'=>$sort,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadother($id=null)
	{
		if($this->_other===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_other=other::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_other===null)
				throw new CHttpException(500,'The requested other does not exist.');
		}
		return $this->_other;
	}

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->loadother($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}
	}
}
