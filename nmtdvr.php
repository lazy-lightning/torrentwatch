<?php

date_default_timezone_set('America/Los_Angeles');

// change the following paths if necessary
$yii=dirname(__FILE__).'/yii_framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);

// Enable the display of errors during the include process
defined('NMTDVR_DISPLAY_INCLUDE_HEADERS') or define('NMTDVR_DISPLAY_INCLUDE_HEADERS',true);

require_once($yii);
Yii::createWebApplication($config)->run();