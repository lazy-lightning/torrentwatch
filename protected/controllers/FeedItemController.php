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
	 * Shows a particular feeditem.
	 */
	public function actionShow()
	{
		$this->render('show',array('feeditem'=>$this->loadfeedItem()));
	}

	/**
	 * Lists all feeditems.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria(array('select'=>'id,title,status,pubDate,tvEpisode_id,movie_id', 'order'=>'pubDate DESC'));
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
    elseif(isset($_GET['status']) && $_GET['status'] == 'queued')
    {
      $criteria->condition = 't.status = '.feedItem::STATUS_QUEUED;
    }
    else if(false===Yii::app()->getRequest()->getIsAjaxRequest())
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
}
