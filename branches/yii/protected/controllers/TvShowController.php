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
	 * Shows a particular tvshow.
	 */
	public function actionShow()
	{
		$this->render('show',array('tvshow'=>$this->loadtvShow()));
	}

	/**
	 * Creates a new tvshow.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$tvshow=new tvShow;
		if(isset($_POST['tvShow']))
		{
			$tvshow->attributes=$_POST['tvShow'];
			if($tvshow->save())
				$this->redirect(array('show','id'=>$tvshow->id));
		}
		$this->render('create',array('tvshow'=>$tvshow));
	}

	/**
	 * Updates a particular tvshow.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$tvshow=$this->loadtvShow();
		if(isset($_POST['tvShow']))
		{
			$tvshow->attributes=$_POST['tvShow'];
			if($tvshow->save())
				$this->redirect(array('show','id'=>$tvshow->id));
		}
		$this->render('update',array('tvshow'=>$tvshow));
	}

	/**
	 * Deletes a particular tvshow.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadtvShow()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all tvshows.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria;

		$pages=new CPagination(tvShow::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

//		$tvshowList=tvShow::model()->findAll($criteria);
    $tvshowList=tvShow::model()->findAllBySql('SELECT * FROM recentTvShows LIMIT :limit OFFSET :offset',
        array(':offset'=>$pages->pageSize*$pages->currentPage,':limit'=>$pages->pageSize));

		$this->render('list',array(
			'tvshowList'=>$tvshowList,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all tvshows.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$criteria=new CDbCriteria;

		$pages=new CPagination(tvShow::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$sort=new CSort('tvShow');
		$sort->applyOrder($criteria);

		$tvshowList=tvShow::model()->findAll($criteria);

		$this->render('admin',array(
			'tvshowList'=>$tvshowList,
			'pages'=>$pages,
			'sort'=>$sort,
		));
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

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->loadtvShow($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}
	}
}
