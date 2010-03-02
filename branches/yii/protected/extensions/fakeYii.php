<?php

require_once(dirname(__FILE__).'/fakeCWebApplication.php');
class Yii extends fakeYiiBase {
  static private $instance;

  static function app($loginRequired = true) { return self::$instance; }
  static function createWebApplication($config) 
  {
    if(self::$instance === null) 
    {
      $realpath = rtrim(realpath(dirname(__FILE__).'/../..'), '/');
      self::$instance = self::getFake($realpath.'/yii_framework', $config);
      self::$instance->init();
    }
    return self::$instance;
  }
}

