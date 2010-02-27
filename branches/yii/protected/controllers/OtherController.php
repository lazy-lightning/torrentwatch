<?php

class OtherController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_other;

  public function actions()
  {
    return array(
        'startDownload' => 'startDownloadAction',
        'makeFavorite' => 'makeFavoriteAction',
        'inspect' => 'inspectMediaAction',
    );
  }

	/**
	 * Shows a particular other.
	 */
	public function actionShow()
	{
		$this->render('show',array('other'=>$this->loadother()));
	}

	/**
	 * Lists all others.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria(array(
          'order'=>'lastUpdated DESC',
          'with'=>array(
              'feedItem'=>array(
                  'select'=>'id,status',
                  'condition'=>'feedItem.id IN ('.
                    'SELECT id FROM'.
                    '  (SELECT status,id,other_id FROM feedItem'.
                    '   WHERE other_id NOT NULL'.
                    '   ORDER by status ASC'.
                    '  )'.
                    'GROUP BY other_id)'
              ),
          ),
          'condition'=>'t.id in (select other_id from feedItem where '.
                       'other_id not null)'

    ));
    $pages = null;

		$pages=new CPagination(other::model()->count($criteria));
   	$pages->pageSize=Yii::app()->dvrConfig->webItemsPerLoad;
		$pages->applyLimit($criteria);

		$otherList=other::model()->findAll($criteria);

		$this->render('list',array(
			'otherList'=>$otherList,
			'pages'=>$pages,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadother($id=null)
	{
		if($this->_other===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_other=other::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_other===null)
				throw new CHttpException(500,'The requested other does not exist.');
		}
		return $this->_other;
	}
}
