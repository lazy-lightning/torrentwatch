<?php

class ResetDataController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

  /**
   * response
   * 
   * @var array values to initialize actionResponseWidget object
   */
  protected $response = array('dialog'=>array('header'=>'Reset Data'));

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
				'actions'=>array('all', 'feedItem', 'media', 'list'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

  public function actionAll()
  {
    $transaction = Yii::app()->db->beginTransaction();
    try {
      foreach(array('feedItem', 'feedItem_quality', 'history', 'movie', 'movie_genre', 'other', 'tvEpisode') as $class)
      {
        $model = new $class;
        $model->deleteAll();
      }
      tvShow::model()->deleteAll('id NOT IN (SELECT tvShow_id from favoriteTvShows)');
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      $this->response['dialog']['content'] = 'Failed: '.$e->getMessage();
    }

    // Update feeds
    $feeds = feed::model()->findAll();
    foreach($feeds as $feed)
    {
      $feed->updateFeedItems(False);
    }
    $this->response['dialog']['content'] = 'Reset has been successfull';
    $this->checkFavorites();
    $this->widget('actionResponseWidget', $this->response);
  }

  public function actionFeedItem()
  {
    $transaction = Yii::app()->db->beginTransaction();
    try {
      feedItem::model()->updateAll(array('status'=>feedItem::STATUS_NEW));
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      $this->response['dialog']['content'] = 'Failed: '.$e->getMessage();
    }
    $this->response['dialog']['content'] = 'Reset has been successfull';
    $this->checkFavorites();
    $this->widget('actionResponseWidget', $this->response);
  }

	public function actionList()
	{
    $this->render('list');
	}

  public function actionMedia()
  {
    $transaction = Yii::app()->db->beginTransaction();
    try {
      movie::model()->updateAll(array('status'=>movie::STATUS_NEW));
      other::model()->updateAll(array('status'=>other::STATUS_NEW));
      tvEpisode::model()->updateAll(array('status'=>tvEpisode::STATUS_NEW));
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      $this->response['dialog']['content'] = 'Failed: '.$e->getMessage();
    }
    $this->response['dialog']['content'] = 'Reset has been successfull';
    $this->checkFavorites();
    $this->widget('actionResponseWidget', $this->response);
  }

  protected function checkFavorites()
  {
    $dlManager = Yii::app()->dlManager;
    $dlManager->checkFavorites(feedItem::STATUS_NEW);
    $dlManager->getStarted();
    if(count($started))
    {
      $content = $this->response['dialog']['content'].'<br>Feed items have been started<br>';
      foreach($started as $history)
        $content .= $history->feedItem_title;
      $this->response['dialog']['content'] = $content;
    }
  }
}
