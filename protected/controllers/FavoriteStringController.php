<?php

class FavoriteStringController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_favoritestring;

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
	 * Shows a particular favoritestring.
	 */
	public function actionShow()
	{
		$this->render('show',array('model'=>$this->loadfavoriteString()));
	}

	/**
	 * Creates a new favoritestring.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$favoritestring=new favoriteString;
		if(isset($_POST['favoriteString']))
		{
			$favoritestring->attributes=$_POST['favoriteString'];
			if($favoritestring->save())
				$this->redirect(array('show','id'=>$favoritestring->id));
		}
		$this->render('create',array('favoritestring'=>$favoritestring));
	}

	/**
	 * Updates a particular favoritestring.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$favoritestring=$this->loadfavoriteString();
		if(isset($_POST['favoriteString']))
		{
			$favoritestring->attributes=$_POST['favoriteString'];
			if($favoritestring->save())
				$this->redirect(array('show','id'=>$favoritestring->id));
		}
		$this->render('update',array('favoritestring'=>$favoritestring));
	}

	/**
	 * Deletes a particular favoritestring.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadfavoriteString()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all favoritestrings.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria(array('order'=>'name ASC'));
    $pages=null;

    if(false===Yii::app()->request->getIsAjaxRequest())
    {
  		$pages=new CPagination(favoriteString::model()->count($criteria));
  		$pages->pageSize=self::PAGE_SIZE;
  		$pages->applyLimit($criteria);
    }

		$favoritestringList=favoriteString::model()->findAll($criteria);

		$this->render('list',array(
			'favoriteList'=>$favoritestringList,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all favoritestrings.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$criteria=new CDbCriteria;

		$pages=new CPagination(favoriteString::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$sort=new CSort('favoriteString');
		$sort->applyOrder($criteria);

		$favoritestringList=favoriteString::model()->findAll($criteria);

		$this->render('admin',array(
			'favoritestringList'=>$favoritestringList,
			'pages'=>$pages,
			'sort'=>$sort,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadfavoriteString($id=null)
	{
		if($this->_favoritestring===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_favoritestring=favoriteString::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_favoritestring===null)
        $this->_favoritestring=new favoriteString();
		}
		return $this->_favoritestring;
	}

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->loadfavoriteString($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}
	}
}
