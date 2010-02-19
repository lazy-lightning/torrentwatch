<div id="favorites" class="dialog_window">
 <div class="content">
   <ul class="favTypes">
    <li><a href="#favoriteTvShows"><span>TV Shows</span></a></li>
    <li><a href="#favoriteMovies"><span>Movies</span></a></li>
    <li><a href="#favoriteStrings"><span>Strings</span></a></li>
   </ul>
   <?php
     // Initialze some listData to be reused by all the favorites
     // Loop through the 3 favorites and display them all
     foreach(array('favoriteTvShows', 'favoriteMovies', 'favoriteStrings') as $favType):
       $class = substr($favType, 0, -1); ?>
     <div id="<?php echo $favType; ?>" class="clearFix">
      <ul class="favorite">
       <li><?php echo CHtml::link('New Favorite', array("/$class/create", '#'=>$favType.'-')); ?></li>
       <?php if($$favType !== null): foreach($$favType as $fav): ?>
        <li><?php echo CHtml::link($fav->name, array("/$class/show", 'id'=>$fav->id, '#'=>$favType.'-'.$fav->id)); ?></li>
       <?php endforeach; endif; ?>
      </ul>
      <?php
        if(isset($response[$class]))
          Yii::app()->getController()->renderPartial("/$class/show", array('model'=>$response[$class]));
      ?>
    </div>
   <?php endforeach; ?>
  </div>
 </div>
</div>
