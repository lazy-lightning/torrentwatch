<?php

error_reporting(E_ALL);

// change the following paths if necessary
$yii=dirname(__FILE__).'/yii_framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
set_time_limit(0);

// Enable the display of errors during the include process
defined('NMTDVR_DISPLAY_INCLUDE_ERRORS') or define('NMTDVR_DISPLAY_INCLUDE_ERRORS',true);

require_once($yii);
Yii::createWebApplication($config)->run();
