<?php 
  echo html::beginForm(
      array('/favoriteTvShow/'.($model->isNewRecord ? 'create' : 'update'), 'id'=>$model->id),
      'post', 
      array('class'=>'favinfo', 'id'=>'favoriteTvShow-'.$model->id)
  );
  echo html::errorSummary($model);
if(isset($success) && $success): ?>
  <div class='saved'>Saved</div>
<?php endif; ?>
<div class="favorite_name">
 <?php 
   echo html::activeLabelEx($model, 'tvShow_id').': ';
   if($model->isNewRecord) {
     if(is_numeric($model->tvShow_id)) {
       $model->tvShow_id = (isset($_POST['favoriteTvShow']['tvShow_id'])
         ? $model->tvShow_id = $_POST['favoriteTvShow']['tvShow_id'] 
         : $model->tvShow_id = $model->getRelated('tvShow')->title);
     }
     echo html::activeTextField($model, 'tvShow_id', array('gray'=>'Name of a TV Show'));
   } else {
     echo '<span>'.html::encode($model->name).'</span>';
   }
 ?>
</div>
<div class="favorite_savein">
 <?php echo html::activeLabelEx($model, 'saveIn', array('title'=>'A valid writable directory to pass to the download clients')).': '.
            html::activeTextField($model, 'saveIn', array('title'=>'A valid writable directory to pass to the download clients', 'gray'=>'Valid writable directory')); ?>
</div>
<div class="favorite_episodes">
  <?php echo html::activeLabelEx($model, 'episodes',array('title'=>'A range of episodes to match from.  Set all to 0 or empty to match all episodes')).': <span>S'.
             html::activeTextField($model, 'minSeason', array('class'=>'min', 'title'=>'minimum season to match', 'gray'=>'min')).'-'.
             html::activeTextField($model, 'maxSeason', array('title'=>'maximum season to match', 'gray'=>'max')).' E'.
             html::activeTextField($model, 'minEpisode', array('class'=>'min', 'title'=>'minimum episode to match', 'gray'=>'min')).'-'.
             html::activeTextField($model, 'maxEpisode', array('title'=>'maximum episode to match', 'gray'=>'max')).'</span>'; ?>
</div>
<div class="favorite_feed">
 <?php echo html::activeLabelEx($model, 'feed_id').': '.
            html::dropDownList('favoriteTvShow[feed_id]', $model->feed_id, $feedsListData); ?>
</div>
<div class="favorite_quality">
 <?php 
   echo html::activeLabelEx($model, 'quality').': ';
   $j=0;foreach($model->asa('quality')->qualityIds as $quality) {
     echo html::dropDownList('quality_id['.++$j.']', $quality, $qualitysListData);
   } 
   for(++$j;$j<4;++$j)
     echo html::dropDownList('quality_id['.$j.']', -1, $qualitysListData);
 ?>
</div>
<div class="favorite_onlynewer">
  <?php echo html::activeLabelEx($model, 'onlyNewer', array('title'=>'Only match episodes that are newer than all previously downloaded episodes')).': '.
             html::activeCheckBox($model, 'onlyNewer', array('title'=>'Only match episodes that are newer than all previously downloaded episodes')); ?>
 <p class='clear'></p>
</div>
<div class="favorite_queue">
  <?php echo html::activeLabelEx($model, 'queue', array('title'=>'Do not automatically downloaded.  Queue for user input')).': '.
             html::activeCheckBox($model, 'queue', array('title'=>'Do not automatically download.  Queue for user input')); ?>
</div>
<div class="buttonContainer">
 
  <a class="submitForm button" id="Update" href="#"><?php echo $model->isNewRecord ? 'Create' : 'Update'; ?></a>
  <?php if(!$model->isNewRecord) 
          echo html::link('Delete', array('/favoriteTvShow/delete', 'id'=>$model->id), array('class'=>'button ajaxSubmit')); ?>
  <a class="toggleDialog button" href="#">Close</a>
</div>
<?php if(isset($success,$create) && $success && $create)
        echo "<li id='favoriteTvShow-li-{$model->id}'>".html::link(
          $model->name,
          array('/favoriteTvShow/show', 'id'=>$model->id),
          array('rel'=>'#favoriteTvShow-'.$model->id)
      )."</li>"; ?>
<?php echo html::endForm().(isset($response) ? $response : ''); ?>
<?php if($model->isNewRecord && !empty($validShows)): ?>
  <script type='text/javascript'>
    $('#favoriteTvShow_tvShow_id').autocomplete(<?php echo json_encode($validShows);?>);
  </script>
<?php endif; ?>
