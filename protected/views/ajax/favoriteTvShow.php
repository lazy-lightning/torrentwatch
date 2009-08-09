<?php $feedsListData = isset($feedsListData) ? $feedsListData : $response['feedsListData'];
      $genresListData = isset($genresListData) ? $genresListData : $response['genresListData'];
      $qualitysListData = isset($qualitysListData) ? $qualitysListData : $response['qualitysListData'];
      $htmlId = 'favoriteTvShows-'.$favorite->id;
      echo CHtml::beginForm(
          array($favorite->isNewRecord ? 'createFavorite' : 'updateFavorite', 'id'=>$favorite->id), 
          'post', array('class'=>'favinfo', 'id'=>$htmlId)
      );
      echo CHtml::errorSummary($favorite);
      ?>
 <div class="favorite_name">
  <?php 
    echo CHtml::activeLabelEx($favorite, 'tvShow_id').': ';
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
   <?php echo CHtml::activeLabelEx($favorite, 'episodes').': <span>S'.
              CHtml::activeTextField($favorite, 'minSeason').'-'.
              CHtml::activeTextField($favorite, 'maxSeason').' E'.
              CHtml::activeTextField($favorite, 'minEpisode').'-'.
              CHtml::activeTextField($favorite, 'maxEpisode').'</span>'; ?>
 </div>
 <div class="favorite_feed">
  <?php echo CHtml::activeLabelEx($favorite, 'feed_id').': '.
             CHtml::dropDownList('favoriteTvShow[feed_id]', $favorite->feed_id, $feedsListData); ?>
 </div>
 <div class="favorite_quality">
  <?php 
    echo CHtml::activeLabelEx($favorite, 'quality').': ';
    $j=0;foreach($favorite->quality as $quality) {
      echo CHtml::dropDownList('quality_id['.++$j.']', $quality->id, $qualitysListData);
    } 
    for(++$j;$j<4;++$j)
      echo CHtml::dropDownList('quality_id['.$j.']', -1, $qualitysListData);
  ?>
 </div>
 <div class="favorite_onlynewer">
   <?php echo CHtml::activeLabelEx($favorite, 'onlyNewer').': '.
              CHtml::activeCheckBox($favorite, 'onlyNewer'); ?>
  <p class='clear'></p>
 </div>
 <div class="favorite_queue">
   <?php echo CHtml::activeLabelEx($favorite, 'queue').': '.
              CHtml::activeCheckBox($favorite, 'queue'); ?>
 </div>
 <div class="buttonContainer">
  
   <a class="submitForm button" id="Update" href="#"><?php echo $favorite->isNewRecord ? 'Create' : 'Update'; ?></a>
   <?php if(!$favorite->isNewRecord) 
           echo CHtml::link('Delete', array('deleteFavorite', 'type'=>get_class($favorite), 'id'=>$favorite->id), array('class'=>'button ajaxSubmit')); ?>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
<?php echo CHtml::endForm(); ?>
