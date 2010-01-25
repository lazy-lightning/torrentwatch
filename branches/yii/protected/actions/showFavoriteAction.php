<?php

class showFavoriteAction extends CAction
{
  public function run()
  {
    $this->attachBehavior('loadModel', array('class'=>'loadControllerARModelBehavior'));
    $model=$this->asa('loadModel')->loadModel();
    if(!$model)
      throw new CHttpException(500, 'The requested favorite could not be found.');
    Yii::app()->getController()->render('show', array(
          'model'=>$model,
          'feedsListData'=>feed::getCHtmlListData(),
          'genresListData'=>genre::getCHtmlListData(),
          'qualitysListData'=>quality::getCHtmlListData(),
    ));
  }
}

