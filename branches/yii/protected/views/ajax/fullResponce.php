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
        'responce'=>$responce,
  ));
  Yii::log('start favorites_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_PROFILE);
  $this->renderPartial('favorites_dialog', array(
        'feeds'=>$feeds,
        'genres'=>$genres,
        'qualitys'=>$qualitys,
        'favoriteMovies'=>$favoriteMovies,
        'favoriteStrings'=>$favoriteStrings,
        'favoriteTvShows'=>$favoriteTvShows,
  ));
  Yii::log('start feedItems_container: '.$logger->getExecutionTime(), CLogger::LEVEL_PROFILE);
  $this->renderPartial('feedItems_container', array(
        'movie'=>$movies,
        'other'=>$others,
        'page'=>1,
        'queued'=>$queued,
        'tv'=>$tvEpisodes,
        'tabs'=>array(
          'TV Episodes' =>'tv', 
          'Movies' => 'movie', 
          'Others' => 'other', 
          'Queued' => 'queued',
        ),
  ));
  Yii::log('start history_dialog: '.$logger->getExecutionTime(), CLogger::LEVEL_PROFILE);
  $this->renderPartial('history_dialog', array(
        'history'=>$history,
  ));

  if(isset($responce['dialog']))
  {
    $opts = $responce['dialog'];
    ?>
      <div class="dialog_window" id="actionResponce">
        <div class="content">
          <h2 class="dialog_heading"><?php echo $opts['header']; ?></h2>
          <p><?php echo $opts['content']; ?></p>
          <div class="buttonContainer clearFix">
            <a href="#actionResponce" class="toggleDialog button">Close</a>
          </div>
        </div>
      </div>
    <?php
  }
  $script = array();
  if(isset($responce['showDialog']))
    $hash = $responce['showDialog'];
  elseif(isset($responce['showTab']))
    $script[] = '$.showTab("'.$responce['showTab'].'");';
  elseif(isset($responce['dialog']))
    $hash = '#actionResponce';
  if(isset($hash))
    $script[] = '$.showDialog("'.$hash.'");';

  if(isset($responce['showFavorite']))
    $hash = $responce['showFavorite'];
  else
    $hash = '#favoriteTvShows-';
  $script[] = '$.showFavorite("'.$hash.'");';

  $scriptOut = "<script type='text/javascript'>"."\n$(function() {\n    ".implode("\n    ", $script)."\n});\n</script>\n";
  echo $scriptOut;
 

