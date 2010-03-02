<?php

error_reporting(E_ALL);

// change the following paths if necessary
$yii=dirname(__FILE__).'/yii_framework/yii.php';
$fakeYii=dirname(__FILE__).'/protected/extensions/fakeYii.php';
$config=dirname(__FILE__).'/protected/config/test.php';

// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
defined('UNIT_TEST') or define('UNIT_TEST',true);
set_time_limit(0);

// Enable the display of errors during the include process
defined('NMTDVR_DISPLAY_INCLUDE_ERRORS') or define('NMTDVR_DISPLAY_INCLUDE_ERRORS',true);

if(isset($_GET['f']))
  require_once($fakeYii);
else
  require_once($yii);

Yii::createWebApplication($config)->run();
