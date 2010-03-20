<?php $tvShow = $model->getRelated('tvShow'); ?>
<div id='tvEpisodeDetails-<?php echo $model->id; ?>' class='mediaDetails'>
<div>
  <h2 class='content'><?php echo CHtml::encode($tvShow->title); ?></h2>
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
  <h2><?php echo CHtml::encode($model->episodeString); ?></h2>
  <h2><?php echo CHtml::encode($model->title); ?></h2>
</div>
<?php if(!empty($model->description)): ?>
  <div>
    <span>Description: </span>
    <span class='content'><?php echo CHtml::encode($model->description); ?></span>
  </div>
<?php endif; ?>
</div>
<?php $this->widget('actionResponseWidget', array(
            'append'=>array(
                'parent'=>'#inspector_container',
                'selector'=>'#tvEpisodeDetails-'.$model->id,
                'delete'=>'#inspector_container > .mediaDetails',
            ),
            'showInspector'=>true,
      )); ?>
