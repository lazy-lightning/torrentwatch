<?php

class BaseController
{
  protected function render($view, $data=array())
  { 
    if(count($data))
      extract($data);
    if($view[0] === '/')
      $view = '..'.$view;
    require(Yii::app()->basePath.'/../themes/classic/views/tvEpisode/'.$view.'.php');
  }

  public function renderPartial($view, $data=array())
  {
    $this->render($view, $data);
  }
}

class CActiveRecord { }

require_once('protected/models/tvEpisode.php');
require_once('protected/models/feedItem.php');
require_once('protected/controllers/TvEpisodeController.php');

$controller = new TvEpisodeController;
$controller->actionList();
