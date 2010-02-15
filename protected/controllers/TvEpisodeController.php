<?php

class TvEpisodeController extends BaseController
{
  const PAGE_SIZE=10;

  /**
   * @var string specifies the default action to be 'list'.
   */
  public $defaultAction='list';

  /**
   * @var CActiveRecord the currently loaded data model instance.
   */
  private $_tvepisode;

  public function actions()
  {
    return array(
      'startDownload'=>'startDownloadAction',
      'makeFavorite'=>'makeFavoriteAction',
      'inspect'=>'inspectMediaAction',
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
        'actions'=>array(
          'list', 'show', 'inspect',
          'makeFavorite', 'startDownload',
        ),
        'users'=>array('@'),
      ),
      array('deny',  // deny all users
        'users'=>array('*'),
      ),
    );
  }

  /**
   * Shows a particular tvepisode.
   */
  public function actionShow()
  {
    $this->render('show',array('tvepisode'=>$this->loadtvEpisode()));
  }

  /**
   * Lists all tvepisodes.
   */
  public function actionList()
  {
    $pages = null;
    $criteria=new CDbCriteria(array(
          'select'=>'id, status, title, season, episode, tvShow_id', 
          'order'=>'t.lastUpdated DESC',
          'with'=>array(
              'tvShow'=>array(
                  'select'=>'id,title',
              ),
              'feedItem'=>array(
                  'select'=>'id,status',
                  'condition'=>'feedItem.id IN ('.
                    'SELECT id FROM'.
                    '  (SELECT status,id,tvEpisode_id FROM feedItem'.
                    '   WHERE tvEpisode_id NOT NULL'.
                    '   ORDER by status ASC'.
                    '  )'.
                    'GROUP BY tvEpisode_id)'
              ),
          ),
          // only display episodes that have a related feeditem
          // how much slower does this make it, should there be an extra column to flag this
          'condition'=>'t.id in (select tvEpisode_id from feedItem where '.
                       'tvEpisode_id not null)'
    ));

    if(isset($_GET['tvShow']))
    {
      $criteria->condition .= ' AND t.tvShow_id = :tvShow_id';
      $criteria->params = array(':tvShow_id'=>$_GET['tvShow']);
    }
    else
      $criteria->condition .= ' AND t.tvShow_id NOT IN '.
        '(SELECT id FROM tvShow WHERE hide = 1)';

    $pages=new CPagination(tvEpisode::model()->count($criteria));
    $pages->pageSize=Yii::app()->dvrConfig->webItemsPerLoad;
    $pages->applyLimit($criteria);

    $tvepisodeList=tvEpisode::model()->findAll($criteria);

    $this->render('list',array(
      'tvepisodeList'=>$tvepisodeList,
      'pages'=>$pages,
    ));
  }

  /**
   * Returns the data model based on the primary key given in the GET variable.
   * If the data model is not found, an HTTP exception will be raised.
   * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
   * @throws CHttpException 500 on tv episode not found
   */
  public function loadtvEpisode($id=null)
  {
    if($this->_tvepisode===null)
    {
      if($id!==null || isset($_GET['id']))
        $this->_tvepisode=tvEpisode::model()->findbyPk($id!==null ? $id : $_GET['id']);
      if($this->_tvepisode===null)
        throw new CHttpException(500,'The requested tvepisode does not exist.');
    }
    return $this->_tvepisode;
  }
}
