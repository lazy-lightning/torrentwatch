<?php
if(!isset($disableCachedOutput) || $disableCachedOutput !== true)
{
  $cached = Yii::app()->getCache()->get('gaya_HD.tvEpisode.list');
  if($cached === false)
  {
    Yii::app()->user->setFlash('gaya_HD.render.show', $tvepisode);
    $this->redirect(array('list'));
  }
  echo str_replace('onloadset="block1"', 'onloadset="hidepopup"', $cached);
}

Yii::import('application.components.allanMc.*');
?>
<!--
  <a href="?day=2010-03-16&block=5&select=2&add=5&color=1" tvid=red></a>
  <a href="?day=2010-03-16&block=5&select=2&add=5&color=2" tvid=green></a>
  <a href="?day=2010-03-16&block=5&select=2&add=5&color=3" tvid=yellow></a>
  <a href="?day=2010-03-16&block=5&select=2&add=5&color=4" tvid=blue></a> 
-->
  <img height=500 id=popupbox src="themes/gaya_HD/images/box.png" width=550>
  <?php
    $screenshot = $tvepisode->tvShow->getBannerLocation();
    if(!file_exists($screenshot))
      $screenshot = 'themes/gaya_HD/images/noimage.png';
  ?>
  <img id=screenshot src="<?php echo $screenshot; ?>">
    <div id=popuptitle>
      <?php echo CHtml::encode($tvepisode->tvShow->title); ?>
    </div>
    <div id=episodeinfo>
    <?php 
    if($tvepisode->episode > 10000)
      echo CHtml::encode($tvepisode->getEpisodeString());
    else
      echo CHtml::encode("Season {$tvepisode->season}, Episode {$tvepisode->episode}:");
    ?></div>
    <div id=episodetitle> <b><?php echo CHtml::encode($tvepisode->title); ?></b></div>
    <?php $block = new tvEpisodeBlock($tvepisode);
          foreach($block->getDescription() as $n => $desc)
      echo "<div id=popuptext".($n+1).">".CHtml::encode($desc)."</div>";
    ?>

      <a href="#block3" name=hidepopup onclick="return popup();" 
          onkeydownset=hidepopup2 onkeyleftset=hidepopup2 
          onkeyrightset=hidepopup onkeyupset=hidepopup tvid=back></a>
      <a href="#block3" name=hidepopup2 onclick="return popup();" 
          onkeydownset=hidepopup onkeyleftset=hidepopup 
          onkeyrightset=hidepopup2 onkeyupset=hidepopup2></a>
  </body>
</html>
