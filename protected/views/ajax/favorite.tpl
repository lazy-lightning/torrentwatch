<?php echo CHtml::beginForm(array('updateFavorite', 'id'=>$favorite->id), 'post', array('class'=>'favinfo', 'id'=>'favorite-'.$i++)); ?>
 <div class="favorite_name">
  <?php 
    echo CHtml::activeLabel($favorite, 'tvShow').': ';
    if($favorite->isNewRecord) {
      echo CHtml::activeTextField($favorite, 'tvShow_id');
    } else {
      echo CHtml::encode($favorite->tvShow->title);
    }
  ?>
 </div>
 <div class="favorite_savein">
  <?php echo CHtml::activeLabel($favorite, 'saveIn'); ?>:
  <?php echo CHtml::activeTextField($favorite, 'saveIn'); ?>
 </div>
 <div class="favorite_episodes">
  <label class="item" title="Regexp Episode filter in form of 2x[1-8]">Episodes:</label>
  <input type="text" name="favoriteTvShow[episodes]" value="" />
 </div>
 <div class="favorite_feed">
  <?php echo CHtml::activeLabel($favorite, 'feedId'); ?>:
  <?php echo CHtml::dropDownList('favoriteTvShow[feed_id]', $favorite->feed_id, CHtml::listData($feeds, 'id', 'title')); ?>
 </div>
 <div class="favorite_quality">
  <?php foreach($favorite->quality as $quality): ?>
    <?php echo CHtml::activeLabel($favorite, 'quality').': '.
               CHtml::dropDownList('quality_id[]', $quality->id, CHtml::listData($qualitys, 'id', 'title')); ?><br>
  <?php endforeach; ?>
 </div>
 <div class="favorite_seedratio">
  <?php echo CHtml::activeLabel($favorite, 'seedRatio').': '.
             CHtml::activeTextField($favorite, 'seedRatio'); ?>
 </div>
 <div class="favorite_onlynewer">
  <?php echo CHtml::activeCheckBox($favorite, 'onlyNewer').' '.
             CHtml::activeLabel($favorite, 'onlyNewer'); ?>
 </div>
 <div class="buttonContainer">
   <a class="submitForm button" id="Update" href="#">Update</a>
   <a class="submitForm button" id="Delete" href="#">Delete</a>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
</form>
