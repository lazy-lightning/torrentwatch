<?php 
  echo CHtml::beginForm(
      array($model->isNewRecord ? 'create' : 'update', 'id'=>$model->id), 
      'post', 
      array('class'=>'favinfo', 'id'=>'favoriteString')
  );
  echo CHtml::errorSummary($model);
?>

 <div class="favorite_name">
  <?php 
    echo CHtml::activeLabelEx($model, 'name').': '.
         CHtml::activeTextField($model, 'name'); ?>
 </div>
 <div class="favorite_savein">
  <?php echo CHtml::activeLabelEx($model, 'saveIn').': '.
             CHtml::activeTextField($model, 'saveIn'); ?>
 </div>
 <div class="favorite_feedId">
  <?php echo CHtml::activeLabelEx($model, 'feedId').': '.
             CHtml::dropDownList('favoriteString[feed_id]', $model->feed_id, feed::getCHtmlListData()); ?>
 </div>
 <div class="favorite_filter">
   <?php echo CHtml::activeLabelEx($model, 'filter').': '.
              CHtml::activeTextField($model, 'filter'); ?>
 </div>
 <div class="favorite_notFilter">
   <?php echo CHtml::activeLabelEx($model, 'notFilter').': '.
              CHtml::activeTextField($model, 'notFilter'); ?>
 </div>
 <div class="favorite_quality">
  <?php  // show min 3 qualitys always, even if less are set
    echo CHtml::activeLabelEx($model, 'quality').': ';
    $qualitysListData = quality::getCHtmlListData();
    $j=0;foreach($model->quality as $quality) {
      echo CHtml::dropDownList('quality_id['.++$j.']', $quality->id, $qualitysListData);
    }
    for(++$j;$j<4;++$j)
      echo CHtml::dropDownList('quality_id['.$j.']', -1, $qualitysListData);
  ?>
 </div>
 <div class="favorite_queue">
   <?php echo CHtml::activeLabelEx($model, 'queue').': '.
              CHtml::activeCheckBox($model, 'queue'); ?>
 </div>
 <div class="buttonContainer">
   <a class="submitForm button" id="Update" href="#"><?php echo $model->isNewRecord ? 'Create' : 'Update'; ?></a>
   <?php if(!$model->isNewRecord)
           echo CHtml::link('Delete', array('delete', 'id'=>$model->id), array('class'=>'button ajaxSubmit')); ?>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
<?php echo CHtml::endForm(); ?>
