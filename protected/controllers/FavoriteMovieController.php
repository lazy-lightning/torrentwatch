<?php

class FavoriteMovieController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_favoritemovie;

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
	 * Shows a particular favoritemovie.
	 */
	public function actionShow()
	{
		$this->render('show',array('model'=>$this->loadfavoriteMovie()));
	}

	/**
	 * Creates a new favoritemovie.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$favoritemovie=new favoriteMovie;
		if(isset($_POST['favoriteMovie']))
		{
			$favoritemovie->attributes=$_POST['favoriteMovie'];
			if($favoritemovie->save())
				$this->redirect(array('show','id'=>$favoritemovie->id));
		}
		$this->render('create',array('favoritemovie'=>$favoritemovie));
	}

	/**
	 * Updates a particular favoritemovie.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$favoritemovie=$this->loadfavoriteMovie();
		if(isset($_POST['favoriteMovie']))
		{
			$favoritemovie->attributes=$_POST['favoriteMovie'];
			if($favoritemovie->save())
				$this->redirect(array('show','id'=>$favoritemovie->id));
		}
		$this->render('update',array('favoritemovie'=>$favoritemovie));
	}

	/**
	 * Deletes a particular favoritemovie.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadfavoriteMovie()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all favoritemovies.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria(array('order'=>'name ASC'));
    $pages = null;

    if(false===Yii::app()->request->getIsAjaxRequest())
    {
  		$pages=new CPagination(favoriteMovie::model()->count($criteria));
  		$pages->pageSize=self::PAGE_SIZE;
  		$pages->applyLimit($criteria);
    }

		$favoritemovieList=favoriteMovie::model()->findAll($criteria);

		$this->render('list',array(
			'favoriteList'=>$favoritemovieList,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all favoritemovies.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$criteria=new CDbCriteria;

		$pages=new CPagination(favoriteMovie::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$sort=new CSort('favoriteMovie');
		$sort->applyOrder($criteria);

		$favoritemovieList=favoriteMovie::model()->findAll($criteria);

		$this->render('admin',array(
			'favoritemovieList'=>$favoritemovieList,
			'pages'=>$pages,
			'sort'=>$sort,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadfavoriteMovie($id=null)
	{
		if($this->_favoritemovie===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_favoritemovie=favoriteMovie::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_favoritemovie===null)
        $this->_favoritemovie=new favoriteMovie();
		}
		return $this->_favoritemovie;
	}

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->loadfavoriteMovie($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}
	}
}
