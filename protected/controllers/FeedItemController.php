<?php

class FeedItemController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_feeditem;

  public function actions()
  {
    return array(
        'makeFavorite'=>'makeFavoriteAction',
        'startDownload'=>'startDownloadAction',
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
				'actions'=>array('list', 'show', 'startDownload', 'makeFavorite'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' actions
				'actions'=>array('admin'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Shows a particular feeditem.
	 */
	public function actionShow()
	{
		$this->render('show',array('feeditem'=>$this->loadfeedItem()));
	}

	/**
	 * Creates a new feeditem.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$feeditem=new feedItem;
		if(isset($_POST['feedItem']))
		{
			$feeditem->attributes=$_POST['feedItem'];
			if($feeditem->save())
				$this->redirect(array('show','id'=>$feeditem->id));
		}
		$this->render('create',array('feeditem'=>$feeditem));
	}

	/**
	 * Updates a particular feeditem.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$feeditem=$this->loadfeedItem();
		if(isset($_POST['feedItem']))
		{
			$feeditem->attributes=$_POST['feedItem'];
			if($feeditem->save())
				$this->redirect(array('show','id'=>$feeditem->id));
		}
		$this->render('update',array('feeditem'=>$feeditem));
	}

	/**
	 * Deletes a particular feeditem.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadfeedItem()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all feeditems.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria(array('select'=>'id,title,status,pubDate', 'order'=>'pubDate DESC'));
    $pages = null;

    if(isset($_GET['related']) && isset($_GET['id']))
    {
      $type = $_GET['related'];
      $id = (int) $_GET['id'];
      $whitelist = array('tvEpisode', 'movie', 'other');
      if(in_array($type, $whitelist))
      {
        $criteria->condition = $type.'_id = :id';
        $criteria->params = array(':id'=>$id);
      }
    }
    else
    {
  		$pages=new CPagination(feedItem::model()->count($criteria));
  		$pages->pageSize=self::PAGE_SIZE;
  		$pages->applyLimit($criteria);
    }

		$feeditemList=feedItem::model()->with(array('feed'=>array('select'=>'id,title')))->findAll($criteria);

		$this->render('list',array(
			'feeditemList'=>$feeditemList,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all feeditems.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$criteria=new CDbCriteria;

		$pages=new CPagination(feedItem::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$sort=new CSort('feedItem');
		$sort->applyOrder($criteria);

		$feeditemList=feedItem::model()->findAll($criteria);

		$this->render('admin',array(
			'feeditemList'=>$feeditemList,
			'pages'=>$pages,
			'sort'=>$sort,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadfeedItem($id=null)
	{
		if($this->_feeditem===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_feeditem=feedItem::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_feeditem===null)
				throw new CHttpException(500,'The requested feeditem does not exist.');
		}
		return $this->_feeditem;
	}

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->loadfeedItem($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}
	}
}
