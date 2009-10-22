<?php

class HistoryController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_history;

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
	 * Shows a particular history.
	 */
	public function actionShow()
	{
		$this->render('show',array('history'=>$this->loadhistory()));
	}

	/**
	 * Creates a new history.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$history=new history;
		if(isset($_POST['history']))
		{
			$history->attributes=$_POST['history'];
			if($history->save())
				$this->redirect(array('show','id'=>$history->id));
		}
		$this->render('create',array('history'=>$history));
	}

	/**
	 * Updates a particular history.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$history=$this->loadhistory();
		if(isset($_POST['history']))
		{
			$history->attributes=$_POST['history'];
			if($history->save())
				$this->redirect(array('show','id'=>$history->id));
		}
		$this->render('update',array('history'=>$history));
	}

	/**
	 * Deletes a particular history.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadhistory()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all historys.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria(array('order'=>'date DESC'));
    $pages = null;

    if(false === Yii::app()->request->getIsAjaxRequest())
    {
  		$pages=new CPagination(history::model()->count($criteria));
  		$pages->pageSize=self::PAGE_SIZE;
  		$pages->applyLimit($criteria);
    }

		$historyList=history::model()->findAll($criteria);

		$this->render('list',array(
			'historyList'=>$historyList,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all historys.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$criteria=new CDbCriteria;

		$pages=new CPagination(history::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$sort=new CSort('history');
		$sort->applyOrder($criteria);

		$historyList=history::model()->findAll($criteria);

		$this->render('admin',array(
			'historyList'=>$historyList,
			'pages'=>$pages,
			'sort'=>$sort,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadhistory($id=null)
	{
		if($this->_history===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_history=history::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_history===null)
				throw new CHttpException(500,'The requested history does not exist.');
		}
		return $this->_history;
	}

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->loadhistory($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}
	}
}
