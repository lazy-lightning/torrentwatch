<?php

class HistoryController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_history;

	/**
	 * Deletes a particular history.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
      $transaction = Yii::app()->getDb()->beginTransaction();
      try {
        if(isset($_GET['all']))
          history::model()->deleteAll();
        else
    			$this->loadhistory()->delete();
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all historys.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria(array('order'=>'date DESC'));
    $pages = null;

    if(false === Yii::app()->request->getIsAjaxRequest())
    {
  		$pages=new CPagination(history::model()->count($criteria));
  		$pages->pageSize=self::PAGE_SIZE;
  		$pages->applyLimit($criteria);
    }

		$historyList=history::model()->findAll($criteria);

		$this->render('list',array(
			'historyList'=>$historyList,
			'pages'=>$pages,
		));
	}

	/**
	 * Shows a particular history.
	 */
	public function actionShow()
	{
		$this->render('show',array('history'=>$this->loadhistory()));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadhistory($id=null)
	{
		if($this->_history===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_history=history::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_history===null)
				throw new CHttpException(500,'The requested history does not exist.');
		}
		return $this->_history;
	}
}
