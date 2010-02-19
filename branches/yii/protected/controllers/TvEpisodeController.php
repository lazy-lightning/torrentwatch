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
    $db = Yii::app()->getDb();
    $params = $pages = null;
    $vars = "e.id as id, e.status as status, e.title as title, e.season as season, e.episode as episode, s.id as tvShow_id, s.title as tvShow_title, i.id as feedItem_id, i.status as feedItem_status";
    $tables = "tvEpisode e, tvShow s, feedItem i";
    $condition = <<<EOD
       s.id = e.tvShow_id
   AND i.tvEpisode_id = e.id
   AND i.id IN (
       SELECT id FROM
         (SELECT status,id,tvEpisode_id
            FROM feedItem
           WHERE tvEpisode_id NOT NULL
           ORDER BY status ASC
         )
       GROUP BY tvEpisode_id)
EOD
    ;
    if(isset($_GET['tvShow']))
      $condition = 's.id = :tvShow_id AND '.$condition;
    else
      $condition = 's.id NOT IN (SELECT id FROM tvShow WHERE hide = 1) AND '.$condition;

    $pages=new CPagination($db->createCommand("SELECT count(*) FROM $tables WHERE $condition")->queryScalar());
    $pageSize = $pages->pageSize=Yii::app()->dvrConfig->webItemsPerLoad;

    $cmd = $db->createCommand(
            "SELECT $vars FROM $tables WHERE $condition ORDER BY e.lastUpdated DESC LIMIT {$pages->pageSize} OFFSET ".($pages->currentPage*$pageSize)
    );
    if(isset($_GET['tvShow']))
      $cmd->bindParam(':tvShow_id', $_GET['tvShow']);

    $this->render('list',array(
      'tvepisodeList'=>$cmd->queryAll(),
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
