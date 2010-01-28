<?php 
  echo CHtml::beginForm(
      array('/favoriteMovie/'.($model->isNewRecord ? 'create' : 'update'), 'id'=>$model->id), 
      'post', 
      array('class'=>'favinfo', 'id'=>'favoriteMovie-'.$model->id)
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
 <div class="favorite_feed">
  <?php echo CHtml::activeLabelEx($model, 'feed_id').': '.
             CHtml::dropDownList('favoriteMovie[feed_id]', $model->feed_id, $feedsListData); ?>
 </div>
 <div class="favorite_rating">
   <?php echo CHtml::activeLabelEx($model, 'rating').': '.
              CHtml::activeTextField($model, 'rating'); ?>
 </div>
 <div class="favorite_years">
   <?php echo CHtml::activeLabel($model, 'year').': <span>'.
              CHtml::activeTextField($model, 'minYear').'-'.
              CHtml::activeTextField($model, 'maxYear').'</span>'; ?>
 </div>
 <div class="favorite_genre">
  <?php echo CHtml::activeLabelEx($model, 'genre_id').': '.
             CHtml::dropDownList('favoriteMovie[genre_id]', $model->genre_id, $genresListData); ?>
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
   <a class="submitForm button" class="update" href="#"><?php echo ($model->isNewRecord ? 'Create' : 'Update'); ?></a>
   <?php if(!$model->isNewRecord)
           echo CHtml::link('Delete', array('delete', 'id'=>$model->id), array('class'=>'button ajaxSubmit')); ?>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
<?php if(isset($addLi) && $addLi)
        echo "<li id='favoriteMovie-li-{$model->id}'>".CHtml::link(
          $model->name,
          array('show', 'id'=>$model->id),
          array('rel'=>'#favoriteMovie-'.$model->id)
        )."</li>";
echo CHtml::endForm().(isset($response) ? $response : ''); ?>

