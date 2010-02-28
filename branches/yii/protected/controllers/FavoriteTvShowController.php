<?php

class FavoriteTvShowController extends BaseController
{
  const PAGE_SIZE=10;

  /**
   * @var string specifies the default action to be 'list'.
   */
  public $defaultAction='list';

  /**
   * @var CActiveRecord the currently loaded data model instance.
   */
  private $_favorite;

  public function actions()
  {
    return array(
        'delete'=>'deleteFavoriteAction',
        'update'=>'updateFavoriteAction',
        'show'=>'showFavoriteAction',
        'create'=>array(
          'class'=>'updateFavoriteAction',
          'create'=>true,
          'beforeRender'=>'createViewVariables',
        ),
    );
  }

  /**
   * Lists all favorites.
   */
  public function actionList($options = array())
  {
    $criteria=new CDbCriteria(array('order'=>'title ASC'));
    $pages=null;
    if(false===Yii::app()->request->getIsAjaxRequest())
    {
      $pages=new CPagination(favoriteTvShow::model()->count($criteria));
      $pages->pageSize=self::PAGE_SIZE;
      $pages->applyLimit($criteria);
      $favoriteList=favoriteTvShow::model()->with(array('tvShow'=>array('select'=>'id,title')))->findAll($criteria);
    }
    else
    {
      $favoriteList=Yii::app()->db->createCommand(
          'SELECT favoriteTvShows.id AS id, tvShow.title AS name FROM favoriteTvShows,tvShow WHERE tvShow.id = favoriteTvShows.tvShow_id ORDER BY tvShow.title ASC'
      )->queryAll();
      // fake it being an object
      foreach($favoriteList as $key=>$value)
        $favoriteList[$key] = (object)$value;
    }

    $this->render('list',array_merge($options, array(
      'favoriteList'=>$favoriteList,
      'pages'=>$pages,
    )));
  }

  /**
   * createViewVariables 
   * 
   * @param favoriteTvShow $model 
   * @return void
   */
  public function createViewVariables($model)
  {
    $out = array();
    if($model->isNewRecord)
    {
      foreach(tvShow::model()->findAll(array('select'=>'title', 'order'=>'title')) as $model)
        $out['validShows'][] = $model->title;
    }
    return $out;
  }
  /**
   * Returns the data model based on the primary key given in the GET variable.
   * If the data model is not found, an HTTP exception will be raised.
   * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
   */
  public function loadfavoriteTvShow($id=null)
  {
    if($this->_favorite===null)
    {
      if($id!==null || isset($_GET['id']))
        $this->_favorite=favoriteTvShow::model()->findbyPk($id!==null ? $id : $_GET['id']);
      if($this->_favorite===null)
        throw new CHttpException(500, 'The requested favorite tv show could not be found.');
    }
    return $this->_favorite;
  }

}
