<?php $htmlId = 'favoriteTvShows-'.$favorite->id;
      echo CHtml::beginForm(array('updateFavorite', 'id'=>$favorite->id), 'post', array('class'=>'favinfo', 'id'=>$htmlId));
      if(isset($responce[$htmlId])) {
        $favorite = $responce[$htmlId];
        echo CHtml::errorSummary($favorite);
//        echo '<pre>';var_dump($favorite);echo '</pre>';
      } ?>
 <div class="favorite_name">
  <?php 
    echo CHtml::activeLabelEx($favorite, 'tvShow').': ';
    if($favorite->isNewRecord) {
      echo CHtml::activeTextField($favorite, 'tvShow_id');
    } else {
      echo CHtml::encode($favorite->name);
    }
  ?>
 </div>
 <div class="favorite_savein">
  <?php echo CHtml::activeLabelEx($favorite, 'saveIn').': '.
             CHtml::activeTextField($favorite, 'saveIn'); ?>
 </div>
 <div class="favorite_episodes">
  <label class="item" title="Regexp Episode filter in form of 2x[1-8]">Episodes:</label>
  <input type="text" name="favoriteTvShow[episodes]" value="" />
 </div>
 <div class="favorite_feed">
  <?php echo CHtml::activeLabelEx($favorite, 'feedId').': '.
             CHtml::dropDownList('favoriteTvShow[feed_id]', $favorite->feed_id, CHtml::listData($feeds, 'id', 'title')); ?>
 </div>
 <div class="favorite_quality">
  <?php 
    echo CHtml::activeLabelEx($favorite, 'quality').': ';
    $j=0;foreach($favorite->quality as $quality) {
      echo CHtml::dropDownList('quality_id['.++$j.']', $quality->id, CHtml::listData($qualitys, 'id', 'title'));
    } 
    for(++$j;$j<4;++$j)
      echo CHtml::dropDownList('quality_id['.$j.']', -1, CHtml::listData($qualitys, 'id', 'title'));
  ?>
 </div>
 <div class="favorite_onlynewer">
  <?php echo CHtml::activeCheckBox($favorite, 'onlyNewer').' '.
             CHtml::activeLabelEx($favorite, 'onlyNewer'); ?>
 </div>
 <div class="buttonContainer">
   <a class="submitForm button" id="Update" href="#">Update</a>
   <a class="submitForm button" id="Delete" href="#">Delete</a>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
</form>
