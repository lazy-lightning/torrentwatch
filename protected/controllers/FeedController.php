<?php

class FeedController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_feed;

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
	 * Shows a particular feed.
	 */
	public function actionShow()
	{
		$this->render('show',array('feed'=>$this->loadfeed()));
	}

	/**
	 * Creates a new feed.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$feed=new feed;
		if(isset($_POST['feed']))
		{
			$feed->attributes=$_POST['feed'];
			if($feed->save())
				$this->redirect(array('show','id'=>$feed->id));
		}
		$this->render('create',array('feed'=>$feed));
	}

	/**
	 * Updates a particular feed.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$feed=$this->loadfeed();
		if(isset($_POST['feed']))
		{
			$feed->attributes=$_POST['feed'];
			if($feed->save())
				$this->redirect(array('show','id'=>$feed->id));
		}
		$this->render('update',array('feed'=>$feed));
	}

	/**
	 * Deletes a particular feed.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadfeed()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all feeds.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria;

		$pages=new CPagination(feed::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$feedList=feed::model()->findAll($criteria);

		$this->render('list',array(
			'feedList'=>$feedList,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all feeds.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$criteria=new CDbCriteria;

		$pages=new CPagination(feed::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$sort=new CSort('feed');
		$sort->applyOrder($criteria);

		$feedList=feed::model()->findAll($criteria);

		$this->render('admin',array(
			'feedList'=>$feedList,
			'pages'=>$pages,
			'sort'=>$sort,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadfeed($id=null)
	{
		if($this->_feed===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_feed=feed::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_feed===null)
				throw new CHttpException(500,'The requested feed does not exist.');
		}
		return $this->_feed;
	}

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->loadfeed($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}

    if (isset($_GET['command']) && $_GET['command']==='updateFeedItems')
    {
      if(isset($_GET['id'])) {
        Yii::log('performing single update', CLogger::LEVEL_ERROR);
        $this->loadfeed()->updateFeedItems();
        Yii::log('update complete', CLogger::LEVEL_ERROR);
      } else {
        foreach(feed::model()->findAll() as $feed)
          $feed->updateFeedItems();
      }
    }
	}
}
