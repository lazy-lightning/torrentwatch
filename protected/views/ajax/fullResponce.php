<?php
  define('VIEWPATH','protected/views/ajax/');
  $logger = Yii::getLogger();
  Yii::log('start configuration_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_PROFILE);
  include VIEWPATH.'configuration_dialog.php';
  Yii::log('start favorites_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_PROFILE);
  include VIEWPATH.'favorites_dialog.php';
  Yii::log('start feedItems_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_PROFILE);
  include VIEWPATH.'feedItems_container.php';
  Yii::log('start history_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_PROFILE);
  include VIEWPATH.'history_dialog.php';

  if(isset($responce['dialog']))
  {
    $opts = $responce['dialog'];
    ?>
      <div class="dialog_window" id="actionResponce">
        <h1><?php echo $opts['header']; ?></h1>
        <p><?php echo $opts['content']; ?></p>
        <div class="buttonContainer">
          <a href="#actionResponce" class="toggleDialog button">Close</a>
        </div>
      </div>
    <?php
  }
  if(isset($responce['showDialog']))
    $hash = $responce['showDialog'];
  elseif(isset($responce['showTab']))
    echo '<script type="text/javascript">$.showTab("'.$responce['showTab'].'");</script>';
  elseif(isset($responce['dialog']))
    $hash = '#actionResponce';
  if(isset($hash))
    echo '<script type="text/javascript">$.showDialog("'.$hash.'");</script>';

  if(isset($responce['showFavorite']))
    $hash = $responce['showFavorite'];
  else
    $hash = '#favoriteTvShows-';
  echo '<script type="text/javascript">$.showFavorite("'.$hash.'");</script>';
 

