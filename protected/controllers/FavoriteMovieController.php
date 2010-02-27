<?php

class FavoriteMovieController extends BaseController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_favoritemovie;

  public function actions()
  {
    return array(
        'delete' => 'deleteFavoriteAction',
        'show' => 'showFavoriteAction',
        'update' => 'updateFavoriteAction',
        'create'=>array(
          'class'=>'updateFavoriteAction',
          'create'=>true
        ),
     );
  }

	/**
	 * Lists all favoritemovies.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria(array('order'=>'name ASC'));
    $pages = null;

    if(false===Yii::app()->request->getIsAjaxRequest())
    {
  		$pages=new CPagination(favoriteMovie::model()->count($criteria));
  		$pages->pageSize=self::PAGE_SIZE;
  		$pages->applyLimit($criteria);
    }

		$favoritemovieList=favoriteMovie::model()->findAll($criteria);

		$this->render('list',array(
			'favoriteList'=>$favoritemovieList,
			'pages'=>$pages,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadfavoriteMovie($id=null)
	{
		if($this->_favoritemovie===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_favoritemovie=favoriteMovie::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_favoritemovie===null)
        throw new CHttpException(500, 'The requested favorite movie could not be found.');
		}
		return $this->_favoritemovie;
	}
}
