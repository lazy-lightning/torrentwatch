<?php

// change the following paths if necessary
$yiit=dirname(__FILE__).'/../../yii_framework/yiit.php';
$config=dirname(__FILE__).'/../config/test.php';

define('YII_DEBUG', TRUE);
define('YII_TRACE_LEVEL', 1);

require_once($yiit);
require_once(dirname(__FILE__).'/WebTestCase.php');
Yii::createWebApplication($config);
Yii::import('application.tests.unit.*');
