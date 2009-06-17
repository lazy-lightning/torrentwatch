<?php echo CHtml::beginForm(array('updateFavorite', 'id'=>$favorite->id), 'post', array('class'=>'favinfo', 'id'=>'favoriteMovies-'.$favorite->id));
      if(isset($responce['favoriteMovies-'.$favorite->id])) {
        $favorite = $responce['favoriteMovies-'.$favorite->id];
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
 <div class="favorite_feed">
  <?php echo CHtml::activeLabelEx($favorite, 'feedId').': '.
             CHtml::dropDownList('favoriteMovie[feed_id]', $favorite->feed_id, CHtml::listData($feeds, 'id', 'title')); ?>
 </div>
 <div class="favorite_rating">
   <?php echo CHtml::activeLabelEx($favorite, 'rating').': '.
              CHtml::activeTextField($favorite, 'rating'); ?>
 </div>
 <div class="favorite_minYear">
   <?php echo CHtml::activeLabelEx($favorite, 'minYear').': '.
              CHtml::activeTextField($favorite, 'minYear'); ?>
 </div>
 <div class="favorite_maxYear">
   <?php echo CHtml::activeLabelEx($favorite, 'maxYear').': '.
              CHtml::activeTextField($favorite, 'maxYear');?>
 </div>
 <div class="favorite_genre">
  <?php echo CHtml::activeLabelEx($favorite, 'genre_id').': '.
             CHtml::dropDownList('favoriteMovie[genre_id]', $favorite->genre_id, CHtml::listData($genres, 'id', 'title')); ?>
 </div>
 <div class="favorite_quality">
  <?php  // show min 3 qualitys always, even if less are set
    echo CHtml::activeLabelEx($favorite, 'quality').': ';
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





