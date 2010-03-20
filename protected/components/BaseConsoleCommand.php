<?php

abstract class BaseConsoleCommand extends CConsoleCommand
{
  public function __construct($name, $runner)
  {
    error_reporting(E_ALL|E_STRICT);
    date_default_timezone_set(Yii::app()->dvrConfig->timezone);
    return parent::__construct($name,$runner);
  }
}
