<?php echo CHtml::beginForm(array('updateFavorite', 'id'=>$favorite->id), 'post', array('class'=>'favinfo', 'id'=>'favoriteMovies-'.$i++)); ?>
 <div class="favorite_name">
  <?php 
    echo CHtml::activeLabel($favorite, 'name').': ';
         CHtml::activeTextField($favorite, 'name');
  ?>
 </div>
 <div class="favorite_savein">
  <?php echo CHtml::activeLabel($favorite, 'saveIn'); ?>:
  <?php echo CHtml::activeTextField($favorite, 'saveIn'); ?>
 </div>
 <div class="favorite_feed">
  <?php echo CHtml::activeLabel($favorite, 'feedId'); ?>:
  <?php echo CHtml::dropDownList('favoriteTvShow[feed_id]', $favorite->feed_id, CHtml::listData($feeds, 'id', 'title')); ?>
 </div>
 <div class="favorite_rating">
   <?php echo CHtml::activeLabel($favorite, 'rating'); ?>:
   <?php echo CHtml::activeTextField($favorite, 'rating'); ?>
 </div>
 <div class="favorite_genre">
  <?php echo CHtml::activeLabel($favorite, 'genre_id'); ?>:
  <?php echo CHtml::dropDownList('favoriteTvShow[genre_id]', $favorite->genre_id, CHtml::listData($genres, 'id', 'title')); ?>
 </div>
 <div class="favorite_quality">
  <?php echo CHtml::activeLabel($favorite, 'quality'); ?>:
  <?php 
    $j=0;foreach($favorite->qualitys as $quality) {
      echo CHtml::dropDownList('quality_id['.++$j.']', $quality->id, CHtml::listData($qualitys, 'id', 'title'));
    }
    for(;$j<3;++$j)
      echo CHtml::dropDownList('quality_id['.$j.']', -1, CHtml::listData($qualitys, 'id', 'title'));
  ?>
 </div>
 <div class="buttonContainer">
   <a class="submitForm button" id="Update" href="#">Update</a>
   <a class="submitForm button" id="Delete" href="#">Delete</a>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
</form>
