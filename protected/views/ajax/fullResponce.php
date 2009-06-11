<?php
  define('VIEWPATH','protected/views/ajax/');
  $logger = Yii::getLogger();
  Yii::log('start configuration_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_ERROR);
  include VIEWPATH.'configuration_dialog.tpl';
  Yii::log('start favorites_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_ERROR);
  include VIEWPATH.'favorites_dialog.tpl';
//  include VIEWPATH.'feeds_dialog.tpl';
  Yii::log('start feedItems_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_ERROR);
  include VIEWPATH.'feedItems_container.tpl';
  Yii::log('start history_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_ERROR);
  include VIEWPATH.'history_dialog.tpl';

