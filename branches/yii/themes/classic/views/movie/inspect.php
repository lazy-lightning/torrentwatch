<div id='movieDetails-<?php echo $model->id; ?>' class='mediaDetails'>
<div>
  <h2><?php echo CHtml::link($model->fullTitle, $model->imdbLink); ?></h2>
</div>
<?php if(!empty($model->qualityString)): ?>
  <div>
    <span>Quality:</span>
    <span class='content'><?php echo CHtml::encode($model->qualityString); ?></span>
  </div>
<?php endif;
      if(!empty($model->year)): ?>
  <div>
    <span>Year:</span>
    <span class='content'><?php echo CHtml::encode($model->year); ?></span>
  </div>
<?php endif;
      if(!empty($model->genreString)): ?>
  <div>
    <span>Genre:</span>
    <span class='content'><?php echo CHtml::encode($model->genreString); ?></span>
  </div>
<?php endif;   
      if(!empty($model->rating)): ?>
<div>
  <span>Rating:</span>
  <span class='content'><?php echo CHtml::encode($model->rating); ?> / 100</span>
</div>
<?php endif;
      if(!empty($model->plot)): ?>
  <div>
    <span>Plot:</span>
    <span class='content'><?php echo CHtml::encode($model->plot); ?></span>
  </div>
<?php endif; 
      if(!empty($model->runtime)): ?>
  <div>
    <span>Runtime:</span>
    <span class='content'><?php echo CHtml::encode($model->runtime); ?> minutes</span>
  </div>
<?php endif; ?>
</div>
<?php $this->widget('actionResponseWidget', array(
            'append'=>array(
                'parent'=>'#inspector_container',
                'selector'=>'#movieDetails-'.$model->id,
                'delete'=>'#inspector_container > .mediaDetails',
            ),
            'showInspector'=>true,
      )); ?>

