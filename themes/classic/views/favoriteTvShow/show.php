<?php 
  echo CHtml::beginForm(
      array('/favoriteTvShow/'.($model->isNewRecord ? 'create' : 'update'), 'id'=>$model->id),
      'post', 
      array('class'=>'favinfo', 'id'=>'favoriteTvShow-'.$model->id)
  );
  echo CHtml::errorSummary($model);
if(isset($success) && $success): ?>
  <div class='saved'>Saved</div>
<?php endif; ?>
<div class="favorite_name">
 <?php 
   echo CHtml::activeLabelEx($model, 'tvShow_id').': ';
   if($model->isNewRecord) {
     if(is_numeric($model->tvShow_id)) {
       $model->tvShow_id = (isset($_POST['favoriteTvShow']['tvShow_id'])
         ? $model->tvShow_id = $_POST['favoriteTvShow']['tvShow_id'] 
         : $model->tvShow_id = $model->getRelated('tvShow')->title);
     }
     echo CHtml::activeTextField($model, 'tvShow_id');
   } else {
     echo '<span>'.CHtml::encode($model->name).'</span>';
   }
 ?>
</div>
<div class="favorite_savein">
 <?php echo CHtml::activeLabelEx($model, 'saveIn', array('title'=>'A valid writable directory to pass to the download clients')).': '.
            CHtml::activeTextField($model, 'saveIn', array('title'=>'A valid writable directory to pass to the download clients')); ?>
</div>
<div class="favorite_episodes">
  <?php echo CHtml::activeLabelEx($model, 'episodes',array('title'=>'A range of episodes to match from.  Set all to 0 or empty to match all episodes')).': <span>S'.
             CHtml::activeTextField($model, 'minSeason', array('class'=>'min', 'title'=>'minimum season to match')).'-'.
             CHtml::activeTextField($model, 'maxSeason', array('title'=>'maximum season to match')).' E'.
             CHtml::activeTextField($model, 'minEpisode', array('class'=>'min', 'title'=>'minimum episode to match')).'-'.
             CHtml::activeTextField($model, 'maxEpisode', array('title'=>'maximum episode to match')).'</span>'; ?>
</div>
<div class="favorite_feed">
 <?php echo CHtml::activeLabelEx($model, 'feed_id').': '.
            CHtml::dropDownList('favoriteTvShow[feed_id]', $model->feed_id, $feedsListData); ?>
</div>
<div class="favorite_quality">
 <?php 
   echo CHtml::activeLabelEx($model, 'quality').': ';
   $j=0;foreach($model->asa('quality')->qualityIds as $quality) {
     echo CHtml::dropDownList('quality_id['.++$j.']', $quality, $qualitysListData);
   } 
   for(++$j;$j<4;++$j)
     echo CHtml::dropDownList('quality_id['.$j.']', -1, $qualitysListData);
 ?>
</div>
<div class="favorite_onlynewer">
  <?php echo CHtml::activeLabelEx($model, 'onlyNewer', array('title'=>'Only match episodes that are newer than all previously downloaded episodes')).': '.
             CHtml::activeCheckBox($model, 'onlyNewer', array('title'=>'Only match episodes that are newer than all previously downloaded episodes')); ?>
 <p class='clear'></p>
</div>
<div class="favorite_queue">
  <?php echo CHtml::activeLabelEx($model, 'queue', array('title'=>'Do not automatically downloaded.  Queue for user input')).': '.
             CHtml::activeCheckBox($model, 'queue', array('title'=>'Do not automatically download.  Queue for user input')); ?>
</div>
<div class="buttonContainer">
 
  <a class="submitForm button" id="Update" href="#"><?php echo $model->isNewRecord ? 'Create' : 'Update'; ?></a>
  <?php if(!$model->isNewRecord) 
          echo CHtml::link('Delete', array('/favoriteTvShow/delete', 'id'=>$model->id), array('class'=>'button ajaxSubmit')); ?>
  <a class="toggleDialog button" href="#">Close</a>
</div>
<?php if(isset($success,$create) && $success && $create)
        echo "<li id='favoriteTvShow-li-{$model->id}'>".CHtml::link(
          $model->name,
          array('/favoriteTvShow/show', 'id'=>$model->id),
          array('rel'=>'#favoriteTvShow-'.$model->id)
      )."</li>"; ?>
<?php echo CHtml::endForm().(isset($response) ? $response : ''); ?>
<?php if($model->isNewRecord && !empty($validShows)): ?>
  <script type='text/javascript'>
    $('#favoriteTvShow_tvShow_id').autocomplete(<?php echo json_encode($validShows);?>);
  </script>
<?php endif; ?>
