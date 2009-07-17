<?php

abstract class BaseConsoleCommand extends CConsoleCommand
{
  public function init()
  {
    date_default_timezone_set(Yii::app()->dvrConfig->timezone);
    return parent::init();
  }
}
