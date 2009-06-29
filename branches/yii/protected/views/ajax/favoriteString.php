<?php echo CHtml::beginForm(array('updateFavorite', 'id'=>$favorite->id), 'post', array('class'=>'favinfo', 'id'=>'favoriteStrings-'.$favorite->id));
      if(isset($responce['favoriteStrings-'.$favorite->id])) {
        $favorite = $responce['favoriteStrings-'.$favorite->id];
        echo CHtml::errorSummary($favorite);
      } ?>

 <div class="favorite_name">
  <?php 
    echo CHtml::activeLabelEx($favorite, 'name').': '.
         CHtml::activeTextField($favorite, 'name'); ?>
 </div>
 <div class="favorite_savein">
  <?php echo CHtml::activeLabelEx($favorite, 'saveIn').': '.
             CHtml::activeTextField($favorite, 'saveIn'); ?>
 </div>
 <div class="favorite_feedId">
  <?php echo CHtml::activeLabelEx($favorite, 'feedId').': '.
             CHtml::dropDownList('favoriteString[feed_id]', $favorite->feed_id, $feedsListData); ?>
 </div>
 <div class="favorite_filter">
   <?php echo CHtml::activeLabelEx($favorite, 'filter').': '.
              CHtml::activeTextField($favorite, 'filter'); ?>
 </div>
 <div class="favorite_notFilter">
   <?php echo CHtml::activeLabelEx($favorite, 'notFilter').': '.
              CHtml::activeTextField($favorite, 'notFilter'); ?>
 </div>
 <div class="favorite_quality">
  <?php  // show min 3 qualitys always, even if less are set
    echo CHtml::activeLabelEx($favorite, 'quality').': ';
    $j=0;foreach($favorite->quality as $quality) {
      echo CHtml::dropDownList('quality_id['.++$j.']', $quality->id, $qualitysListData);
    }
    for(++$j;$j<4;++$j)
      echo CHtml::dropDownList('quality_id['.$j.']', -1, $qualitysListData);
  ?>
 </div>
 <div class="favorite_queue">
  <?php echo CHtml::activeCheckBox($favorite, 'queue').' '.
             CHtml::activeLabelEx($favorite, 'queue'); ?>
 </div>
 <div class="buttonContainer">
   <a class="submitForm button" id="Update" href="#">Update</a>
   <a class="submitForm button" id="Delete" href="#">Delete</a>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
</form>
