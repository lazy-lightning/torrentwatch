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
 

