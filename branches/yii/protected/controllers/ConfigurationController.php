<?php

class ConfigurationController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_configuration;

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
	 * Shows a particular configuration.
	 */
	public function actionShow()
	{
		$this->render('show',array('configuration'=>$this->loadconfiguration()));
	}

	/**
	 * Creates a new configuration.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$configuration=new configuration;
		if(isset($_POST['configuration']))
		{
			$configuration->attributes=$_POST['configuration'];
			if($configuration->save())
				$this->redirect(array('show','id'=>$configuration->id));
		}
		$this->render('create',array('configuration'=>$configuration));
	}

	/**
	 * Updates a particular configuration.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$configuration=$this->loadconfiguration();
		if(isset($_POST['configuration']))
		{
			$configuration->attributes=$_POST['configuration'];
			if($configuration->save())
				$this->redirect(array('show','id'=>$configuration->id));
		}
		$this->render('update',array('configuration'=>$configuration));
	}

	/**
	 * Deletes a particular configuration.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadconfiguration()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all configurations.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria;

		$pages=new CPagination(configuration::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$configurationList=configuration::model()->findAll($criteria);

		$this->render('list',array(
			'configurationList'=>$configurationList,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all configurations.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$criteria=new CDbCriteria;

		$pages=new CPagination(configuration::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$sort=new CSort('configuration');
		$sort->applyOrder($criteria);

		$configurationList=configuration::model()->findAll($criteria);

		$this->render('admin',array(
			'configurationList'=>$configurationList,
			'pages'=>$pages,
			'sort'=>$sort,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadconfiguration($id=null)
	{
		if($this->_configuration===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_configuration=configuration::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_configuration===null)
				throw new CHttpException(500,'The requested configuration does not exist.');
		}
		return $this->_configuration;
	}

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->loadconfiguration($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}
	}
}
