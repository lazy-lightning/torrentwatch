<?php $tvShow = $tvEpisode->tvShow; ?>
<div>
  <h2 class='content'><?php 
    if(empty($tvShow->tvdbId))
      echo CHtml::encode($tvShow->title);
    else
      echo CHtml::link($tvShow->title, 'http://thetvdb.com/?tab=series&id='.$tvShow->tvdbId);
    ?>
  </h2>
</div>
<?php if(!empty($tvShow->network_id)): ?>
  <div>
    <span>Network: </span>
    <span class='content'><?php echo CHtml::encode($tvShow->network->title); ?></span>
  </div>
<?php endif; ?>
<?php if(!empty($tvShow->rating)): ?>
  <div>
    <span>Rating: </span>
    <span class='content'><?php echo CHtml::encode($tvShow->rating); ?> / 10</span>
  </div>
<?php endif; ?>
<?php if(!empty($tvShow->description)): ?>
  <div>
    <span>Description: </span>
    <span class='content'><?php echo CHtml::encode($tvShow->shortDescription); ?></span>
  </div>
<?php endif; ?>
<br><br>
<div>
  <h2><?php echo CHtml::encode($tvEpisode->episodeString); ?></h2>
  <h2><?php echo CHtml::encode($tvEpisode->title); ?></h2>
</div>
<?php if(!empty($tvEpisode->description)): ?>
  <div>
    <span>Description: </span>
    <span class='content'><?php echo CHtml::encode($tvEpisode->description); ?></span>
  </div>
<?php endif; ?>
