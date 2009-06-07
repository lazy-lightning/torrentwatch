<?php

class GenreController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_genre;

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
	 * Shows a particular genre.
	 */
	public function actionShow()
	{
		$this->render('show',array('genre'=>$this->loadgenre()));
	}

	/**
	 * Creates a new genre.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$genre=new genre;
		if(isset($_POST['genre']))
		{
			$genre->attributes=$_POST['genre'];
			if($genre->save())
				$this->redirect(array('show','id'=>$genre->id));
		}
		$this->render('create',array('genre'=>$genre));
	}

	/**
	 * Updates a particular genre.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$genre=$this->loadgenre();
		if(isset($_POST['genre']))
		{
			$genre->attributes=$_POST['genre'];
			if($genre->save())
				$this->redirect(array('show','id'=>$genre->id));
		}
		$this->render('update',array('genre'=>$genre));
	}

	/**
	 * Deletes a particular genre.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadgenre()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all genres.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria;

		$pages=new CPagination(genre::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$genreList=genre::model()->findAll($criteria);

		$this->render('list',array(
			'genreList'=>$genreList,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all genres.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$criteria=new CDbCriteria;

		$pages=new CPagination(genre::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$sort=new CSort('genre');
		$sort->applyOrder($criteria);

		$genreList=genre::model()->findAll($criteria);

		$this->render('admin',array(
			'genreList'=>$genreList,
			'pages'=>$pages,
			'sort'=>$sort,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadgenre($id=null)
	{
		if($this->_genre===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_genre=genre::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_genre===null)
				throw new CHttpException(500,'The requested genre does not exist.');
		}
		return $this->_genre;
	}

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->loadgenre($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}
	}
}