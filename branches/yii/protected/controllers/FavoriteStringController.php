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

  public function actions()
  {
    return array(
        'delete' => 'deleteFavoriteAction',
        'show' => 'showFavoriteAction',
        'update' => 'updateFavoriteAction',
        'create'=>array(
          'class'=>'updateFavoriteAction',
          'create'=>true
        ),
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
			array('allow', // allow authenticated user
				'actions'=>array('create','update', 'list', 'show', 'delete'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
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
        throw new CHttpException(500, 'The requested favorite string could not be found.');
		}
		return $this->_favoritestring;
	}
}