<?php
  define('VIEWPATH','protected/views/ajax/');
  $logger = Yii::getLogger();
  $this->renderPartial('resetData');
  $this->renderPartial('welcome_dialog', array(
        'availClients'=>$availClients,
        'config'=>$config,
  ));

  Yii::log('start configuration_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_PROFILE);
  $this->renderPartial('configuration_dialog', array(
        'config'=>$config,
        'availClients'=>$availClients,
        'feeds'=>$feeds,
        'response'=>$response,
  ));
  Yii::log('start favorites_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_PROFILE);
  $this->renderPartial('favorites_dialog', array(
        'favoriteMovies'=>$favoriteMovies,
        'favoriteStrings'=>$favoriteStrings,
        'favoriteTvShows'=>$favoriteTvShows,
        'response'=>$response,
  ));

  if(isset($response['dialog']))
  {
    $opts = $response['dialog'];
    ?>
      <div class="dialog_window" id="actionResponse">
        <div class="content">
          <h2 class="dialog_heading"><?php echo $opts['header']; ?></h2>
          <p><?php echo isset($opts['content']) ? $opts['content'] : ' '; ?></p>
          <div class="buttonContainer clearFix">
            <a href="#actionResponse" class="toggleDialog button">Close</a>
          </div>
        </div>
      </div>
    <?php
  }
  $script = array();
  if(isset($response['showDialog']))
    $hash = $response['showDialog'];
  elseif(isset($response['showTab']))
    $script[] = '$.showTab("'.$response['showTab'].'");';
  elseif(isset($response['dialog']))
    $hash = '#actionResponse';
  if(isset($hash))
    $script[] = '$.showDialog("'.$hash.'");';

  if(isset($response['showFavorite']))
    $script[] = '$.showFavorite("'.$response['showFavorite'].'");';
 
  if(count($script)) {
    $scriptOut = "<script type='text/javascript'>"."\n$(function() {\n    ".implode("\n    ", $script)."\n});\n</script>\n";
    echo $scriptOut;
  }
 

