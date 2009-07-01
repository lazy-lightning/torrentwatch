<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
  'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
  'name'=>'NMTDVR',

  // preloading 'log' component
  'preload'=>array('log'),

  // autoloading model and component classes
  'import'=>array(
    'application.models.*',
    'application.components.*',
    'application.components.feedAdapters.*',
    'application.components.downloadClients.*',
  ),

  // application components
  'components'=>array(
    'dlManager'=>array(
      'class'=>'downloadManager',
    ),
    'dvrConfig'=>array(
      'class'=>'dvrConfig',
    ),
    'log'=>array(
      'class'=>'CLogRouter',
      'routes'=>array(
        array(
          'class'=>'CFileLogRoute',
          'levels'=>'error, warning',
        ),
      ),
    ),
    'user'=>array(
      // enable cookie-based authentication
      'allowAutoLogin'=>true,
    ),
    'db'=>array(
      'connectionString'=>'sqlite:protected/data/source.db',
    ),
  ),

  // application-level parameters that can be accessed
  // using Yii::app()->params['paramName']
  'params'=>array(
    // this is used in contact page
    'adminEmail'=>'webmaster@example.com',
    'dateFormat'=>'m-d-Y h:i a',
  ),
);
