<?php

class BaseController
{
  protected function render($view, $data)
  { 
    extract($data);
    require(Yii::app()->basePath.'/../themes/classic/views/tvEpisode/'.$view.'.php');
  }
}

class CActiveRecord { }

require_once('protected/models/tvEpisode.php');
require_once('protected/models/feedItem.php');
require_once('protected/controllers/TvEpisodeController.php');

date_default_timezone_set(Yii::app()->getComponent('dvrConfig')->timezone);
$controller = new TvEpisodeController;
$controller->actionList();
