<?php
return CMap::mergeArray(
  require(dirname(__FILE__).'/main.php'),
  array(
    'import'=>array(
      'application.tests.*',
    ),
    'components'=>array(
      'dvrConfig'=>array(
        'class'=>'application.components.dvrConfig',
        '_apcKey'=>'',
      ),
      'fixture'=>array(
        'class'=>'application.tests.DbFixtureManager',
        'basePath'=>dirname(__FILE__).'/../tests/fixtures/',
      ),
      'db'=>array(
        'class'=>'application.components.SqliteConnection',
        'connectionString'=>'sqlite:'.dirname(__FILE__).'/../data/source-test.db',
        'enableParamLogging'=>true,
      ),
    ),
  )
);
