<?php $tvShow = $tvEpisode->tvShow; ?>
<div>
  <span>Title: </span>
  <span><?php 
    if(empty($tvShow->tvdbId))
      echo CHtml::encode($tvShow->title);
    else
      echo CHtml::link($tvShow->title, 'http://thetvdb.com/?tab=series&id='.$tvShow->tvdbId);
    ?>
  </span>
</div>
<div>
  <span>Network: </span>
  <span><?php echo CHtml::encode($tvShow->network->title); ?></span>
</div>
<div>
  <span>Rating: </span>
  <span><?php echo CHtml::encode($tvShow->rating); ?></span>
</div>
<div>
  <span>Description: </span>
  <span><?php echo CHtml::encode($tvShow->shortDescription); ?></span>
</div>
<br><br>
<div>
  <span><?php echo CHtml::encode($tvEpisode->episodeString); ?></span>
  <span><?php echo CHtml::encode($tvEpisode->title); ?></span>
</div>
<div>
  <span>Description: </span>
  <span><?php echo CHtml::encode($tvEpisode->description); ?></span>
</div>
   
