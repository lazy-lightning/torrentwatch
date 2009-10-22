<?php 
  echo CHtml::beginForm(
      array($model->isNewRecord ? 'create' : 'update', 'id'=>$model->id), 
      'post', 
      array('class'=>'favinfo', 'id'=>'favoriteTvShow')
  );
  echo CHtml::errorSummary($model);
?>
 <div class="favorite_name">
  <?php 
    echo CHtml::activeLabelEx($model, 'tvShow_id').': ';
    if($model->isNewRecord) {
      echo CHtml::activeTextField($model, 'tvShow_id');
    } else {
      echo '<span>'.CHtml::encode($model->name).'</span>';
    }
  ?>
 </div>
 <div class="favorite_savein">
  <?php echo CHtml::activeLabelEx($model, 'saveIn').': '.
             CHtml::activeTextField($model, 'saveIn'); ?>
 </div>
 <div class="favorite_episodes">
   <?php echo CHtml::activeLabelEx($model, 'episodes').': <span>S'.
              CHtml::activeTextField($model, 'minSeason', array('class'=>'min')).'-'.
              CHtml::activeTextField($model, 'maxSeason').' E'.
              CHtml::activeTextField($model, 'minEpisode', array('class'=>'min')).'-'.
              CHtml::activeTextField($model, 'maxEpisode').'</span>'; ?>
 </div>
 <div class="favorite_feed">
  <?php echo CHtml::activeLabelEx($model, 'feed_id').': '.
             CHtml::dropDownList('favoriteTvShow[feed_id]', $model->feed_id, feed::getCHtmlListData()); ?>
 </div>
 <div class="favorite_quality">
  <?php 
    echo CHtml::activeLabelEx($model, 'quality').': ';
    $qualitysListData = quality::getCHtmlListData();
    $j=0;foreach($model->quality as $quality) {
      echo CHtml::dropDownList('quality_id['.++$j.']', $quality->id, $qualitysListData);
    } 
    for(++$j;$j<4;++$j)
      echo CHtml::dropDownList('quality_id['.$j.']', -1, $qualitysListData);
  ?>
 </div>
 <div class="favorite_onlynewer">
   <?php echo CHtml::activeLabelEx($model, 'onlyNewer').': '.
              CHtml::activeCheckBox($model, 'onlyNewer'); ?>
  <p class='clear'></p>
 </div>
 <div class="favorite_queue">
   <?php echo CHtml::activeLabelEx($model, 'queue').': '.
              CHtml::activeCheckBox($model, 'queue'); ?>
 </div>
 <div class="buttonContainer">
  
   <a class="submitForm button" id="Update" href="#"><?php echo $model->isNewRecord ? 'Create' : 'Update'; ?></a>
   <?php if(!$model->isNewRecord) 
           echo CHtml::link('Delete', array('deleteFavorite', 'type'=>get_class($model), 'id'=>$model->id), array('class'=>'button ajaxSubmit')); ?>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
<?php echo CHtml::endForm(); ?>
