<?php echo CHtml::beginForm(array('updateFavorite', 'id'=>$favorite->id), 'post', array('class'=>'favinfo', 'id'=>'favoriteStrings-'.$i++)); ?>
 <div class="favorite_name">
  <?php 
    echo CHtml::activeLabel($favorite, 'name').': '.
         CHtml::activeTextField($favorite, 'name'); ?>
 </div>
 <div class="favorite_savein">
  <?php echo CHtml::activeLabel($favorite, 'saveIn').': '.
             CHtml::activeTextField($favorite, 'saveIn'); ?>
 </div>
 <div class="favorite_feedId">
  <?php echo CHtml::activeLabel($favorite, 'feedId').': '.
             CHtml::dropDownList('favoriteString[feed_id]', $favorite->feed_id, CHtml::listData($feeds, 'id', 'title')); ?>
 </div>
 <div class="favorite_filter">
   <?php echo CHtml::activeLabel($favorite, 'filter').': '.
              CHtml::activeTextField($favorite, 'filter'); ?>
 </div>
 <div class="favorite_notFilter">
   <?php echo CHtml::activeLabel($favorite, 'notFilter').': '.
              CHtml::activeTextField($favorite, 'notFilter'); ?>
 </div>
 <div class="favorite_quality">
  <?php  // show min 3 qualitys always, even if less are set
    echo CHtml::activeLabel($favorite, 'quality').': ';
    $j=0;foreach($favorite->quality as $quality) {
      echo CHtml::dropDownList('quality_id['.++$j.']', $quality->id, CHtml::listData($qualitys, 'id', 'title'));
    }
    for(++$j;$j<4;++$j)
      echo CHtml::dropDownList('quality_id['.$j.']', -1, CHtml::listData($qualitys, 'id', 'title'));
  ?>
 </div>
 <div class="buttonContainer">
   <a class="submitForm button" id="Update" href="#">Update</a>
   <a class="submitForm button" id="Delete" href="#">Delete</a>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
</form>