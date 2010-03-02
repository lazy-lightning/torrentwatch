<?php 
  echo html::beginForm(
      array('/favoriteMovie/'.($model->isNewRecord ? 'create' : 'update'), 'id'=>$model->id), 
      'post', 
      array('class'=>'favinfo', 'id'=>'favoriteMovie-'.$model->id)
  );
  echo html::errorSummary($model);
  if(isset($success) && $success): ?>
    <div class='saved'>Saved</div>
<?php endif; ?>
 <div class="favorite_name">
  <?php 
    echo html::activeLabelEx($model, 'name', array('title'=>'Must be unique and does not effect matching')).': '.
         html::activeTextField($model, 'name', array('title'=>'Must be unique and does not effect matching', 'gray'=>'Unique title not effecting matching')); ?>
 </div>
 <div class="favorite_savein">
  <?php echo html::activeLabelEx($model, 'saveIn', array('title'=>'A valid writable directory to pass to the download clients')).': '.
             html::activeTextField($model, 'saveIn', array('title'=>'A valid writable directory to pass to the download clients', 'gray'=>'Valid writable directory')); ?>
 </div>
 <div class="favorite_feed">
  <?php echo html::activeLabelEx($model, 'feed_id').': '.
             html::dropDownList('favoriteMovie[feed_id]', $model->feed_id, $feedsListData); ?>
 </div>
 <div class="favorite_rating">
   <?php echo html::activeLabelEx($model, 'rating', array('title'=>'This minimum rating out of 100 to match')).': '.
              html::activeTextField($model, 'rating', array('title'=>'The minimum rating out of 100 to match', 'gray'=>'Minimum out of 100', 'class'=>'numeric')); ?>
 </div>
 <div class="favorite_years">
   <?php echo html::activeLabel($model, 'year', array('title'=>'Movie must be between these years to be matched')).': <span>'.
              html::activeTextField($model, 'minYear', array('title'=>'The minimum movie year to match', 'gray'=>'min', 'class'=>'numeric')).'-'.
              html::activeTextField($model, 'maxYear', array('title'=>'The maximum movie year to match', 'gray'=>'max', 'class'=>'numeric')).'</span>'; ?>
 </div>
 <div class="favorite_genre">
  <?php echo html::activeLabelEx($model, 'genre_id').': '.
             html::dropDownList('favoriteMovie[genre_id]', $model->genre_id, $genresListData); ?>
 </div>
 <div class="favorite_quality">
  <?php  // show min 3 qualitys always, even if less are set
    echo html::activeLabelEx($model, 'quality').': ';
    $qualitysListData = quality::getCHtmlListData();
    $j=0;foreach($model->quality as $quality) {
      echo html::dropDownList('quality_id['.++$j.']', $quality->id, $qualitysListData);
    }
    for(++$j;$j<4;++$j)
      echo html::dropDownList('quality_id['.$j.']', -1, $qualitysListData);
  ?>
 </div>
 <div class="favorite_queue">
   <?php echo html::activeLabelEx($model, 'queue', array('title'=>'Do not automatically download. Queue for user input')).': '.
              html::activeCheckBox($model, 'queue', array('title'=>'Do not automatically download. Queue for user input')); ?>
 </div>
 <div class="buttonContainer">
   <a class="submitForm button" class="update" href="#"><?php echo ($model->isNewRecord ? 'Create' : 'Update'); ?></a>
   <?php if(!$model->isNewRecord)
           echo html::link('Delete', array('delete', 'id'=>$model->id), array('class'=>'button ajaxSubmit')); ?>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
<?php 
if(isset($success,$create) && $success && $create)
{
  echo "<li id='favoriteMovie-li-{$model->id}'>".html::link(
    $model->name,
    array('/favoriteMovie/show', 'id'=>$model->id),
    array('rel'=>'#favoriteMovie-'.$model->id)
  )."</li>"; 
}
echo html::endForm().(isset($response) ? $response : '');
