<?php
  define('VIEWPATH','protected/views/ajax/');
  $logger = Yii::getLogger();
  Yii::log('start configuration_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_ERROR);
  include VIEWPATH.'configuration_dialog.php';
  Yii::log('start favorites_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_ERROR);
  include VIEWPATH.'favorites_dialog.php';
//  include VIEWPATH.'feeds_dialog.php';
  Yii::log('start feedItems_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_ERROR);
  include VIEWPATH.'feedItems_container.php';
  Yii::log('start history_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_ERROR);
  include VIEWPATH.'history_dialog.php';

  if(isset($responce, $responce['dialog']))
  {
    $opts = $responce['dialog'];
    ?>
      <div class="dialog_window" id="actionResponce" style="display: block">
        <h1><?php echo $opts['header']; ?></h1>
        <p><?php echo $opts['content']; ?></p>
        <div class="buttonContainer">
          <a href="#actionResponce" class="toggleDialog button">Close</a>
        </div>
      </div>
    <?php

  }

