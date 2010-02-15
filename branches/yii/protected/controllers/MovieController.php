<?php

class MovieController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_movie;

  public function actions()
  {
    return array(
      'startDownload'=>'startDownloadAction',
      'makeFavorite'=>'makeFavoriteAction',
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
			array('allow', // allow authenticated user to perform actions
				'actions'=>array('list', 'show', 'makeFavorite', 'startDownload'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Shows a particular movie.
	 */
	public function actionShow()
	{
		$this->render('show',array('movie'=>$this->loadmovie()));
	}

	/**
	 * Lists all movies.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria(array(
          'select'=>'id,status,title,name,year,rating',
          'order'=>'lastUpdated DESC',
          'with'=>array(
              'feedItem'=>array(
                  'select'=>'id,status',
                  'condition'=>'feedItem.id IN ('.
                    'SELECT id FROM'.
                    '  (SELECT status,id,movie_id FROM feedItem'.
                    '   WHERE movie_id NOT NULL'.
                    '   ORDER by status DESC'.
                    '  )'.
                    'GROUP BY movie_id)'
              ),
          ),
          'condition'=>'t.id in (select movie_id from feedItem where '.
                       'movie_id not null)'
    ));

		$pages=new CPagination(movie::model()->count($criteria));
		$pages->pageSize=Yii::app()->dvrConfig->webItemsPerLoad;
		$pages->applyLimit($criteria);

		$movieList=movie::model()->findAll($criteria);

		$this->render('list',array(
			'movieList'=>$movieList,
			'pages'=>$pages,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadmovie($id=null)
	{
		if($this->_movie===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_movie=movie::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_movie===null)
				throw new CHttpException(500,'The requested movie does not exist.');
		}
		return $this->_movie;
	}
}
