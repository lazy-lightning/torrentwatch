<?php 
  echo html::beginForm(
      array('/favoriteString/'.($model->isNewRecord ? 'create' : 'update'), 'id'=>$model->id), 
      'post', 
      array('class'=>'favinfo', 'id'=>'favoriteString-'.$model->id)
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
 <div class="favorite_feedId">
  <?php echo html::activeLabelEx($model, 'feedId').': '.
             html::dropDownList('favoriteString[feed_id]', $model->feed_id, feed::getCHtmlListData()); ?>
 </div>
 <div class="favorite_filter">
   <?php echo html::activeLabelEx($model, 'filter', array('title'=>'Feed Item title must match this string to be downloaded.  * is accepted')).': '.
              html::activeTextField($model, 'filter', array('title'=>'Feed Item title must match this string to be downloaded.  * is accepted', 'gray'=>'You can use *')); ?>
 </div>
 <div class="favorite_notFilter">
   <?php echo html::activeLabelEx($model, 'notFilter', array('title'=>'Any feed item title matching this filter will be excluded')).': '.
              html::activeTextField($model, 'notFilter', array('title'=>'Any feed item matching this filter will be excluded', 'gray'=>'Feeditem title must not match')); ?>
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
   <?php echo html::activeLabelEx($model, 'queue', array('title'=>'Do not automatically download.  Queue for user input')).': '.
              html::activeCheckBox($model, 'queue', array('title'=>'Do not automatically download.  Queue for user input')); ?>
 </div>
 <div class="buttonContainer">
   <a class="submitForm button" id="Update" href="#"><?php echo $model->isNewRecord ? 'Create' : 'Update'; ?></a>
   <?php if(!$model->isNewRecord)
           echo html::link('Delete', array('delete', 'id'=>$model->id), array('class'=>'button ajaxSubmit')); ?>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
<?php 
if(isset($success,$create) && $success && $create)
{
  echo "<li id='favoriteString-li-{$model->id}'>".html::link(
    $model->name,
    array('/favoriteString/show', 'id'=>$model->id),
    array('rel'=>'#favoriteString-'.$model->id)
  )."</li>";
}
echo html::endForm().(isset($response) ? $response : '');
