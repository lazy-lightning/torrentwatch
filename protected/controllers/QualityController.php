<?php

class QualityController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_quality;

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
	 * Shows a particular quality.
	 */
	public function actionShow()
	{
		$this->render('show',array('quality'=>$this->loadquality()));
	}

	/**
	 * Creates a new quality.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$quality=new quality;
		if(isset($_POST['quality']))
		{
			$quality->attributes=$_POST['quality'];
			if($quality->save())
				$this->redirect(array('show','id'=>$quality->id));
		}
		$this->render('create',array('quality'=>$quality));
	}

	/**
	 * Updates a particular quality.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$quality=$this->loadquality();
		if(isset($_POST['quality']))
		{
			$quality->attributes=$_POST['quality'];
			if($quality->save())
				$this->redirect(array('show','id'=>$quality->id));
		}
		$this->render('update',array('quality'=>$quality));
	}

	/**
	 * Deletes a particular quality.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadquality()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all qualitys.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria;

		$pages=new CPagination(quality::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$qualityList=quality::model()->findAll($criteria);

		$this->render('list',array(
			'qualityList'=>$qualityList,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all qualitys.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$criteria=new CDbCriteria;

		$pages=new CPagination(quality::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$sort=new CSort('quality');
		$sort->applyOrder($criteria);

		$qualityList=quality::model()->findAll($criteria);

		$this->render('admin',array(
			'qualityList'=>$qualityList,
			'pages'=>$pages,
			'sort'=>$sort,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadquality($id=null)
	{
		if($this->_quality===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_quality=quality::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_quality===null)
				throw new CHttpException(500,'The requested quality does not exist.');
		}
		return $this->_quality;
	}

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->loadquality($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}
	}
}
