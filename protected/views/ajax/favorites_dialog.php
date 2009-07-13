<div id="favorites" class="dialog_window">
 <ul class="favTypes">
  <li><a href="#favoriteTvShows"><span>TV Shows</span></a></li>
  <li><a href="#favoriteMovies"><span>Movies</span></a></li>
  <li><a href="#favoriteStrings"><span>Strings</span></a></li>
 </ul> 
 <?php
   // Initialze some listData to be reused by all the favorites
   $genresListData = CHtml::listData($genres, 'id', 'title');
   $feedsListData = CHtml::listData($feeds, 'id', 'title');
   $qualitysListData = CHtml::listData($qualitys, 'id', 'title');
   // Loop through the 3 favorites and display them all
   foreach(array('favoriteTvShows', 'favoriteMovies', 'favoriteStrings') as $favType):
     $class = substr($favType, 0, -1); ?>
   <div id="<?php echo $favType; ?>">
    <ul class="favorite">
     <li><a href="#<?php echo $favType; ?>-">New Favorite</a></li>
     <?php if($$favType !== null): 
            foreach($$favType as $fav): ?>
       <li><a href="#<?php echo $favType.'-'.$fav->id.'">'.$fav->name;?></a></li>
      <?php endforeach;
           endif; ?>
    </ul>
    <?php 
      // Empty favorite seen as 'New Favorite' above
      $favorite = new $class;
      include VIEWPATH.$class.'.php';
      // $$ points to the variable named in the named variable
      // so $favoriteTvShows, or whatever
      if($$favType !== null) {
        foreach($$favType as $favorite) 
          include VIEWPATH.$class.'.php';
      }
    ?>
    <div class='clear'></div>
  </div>
 <?php endforeach; ?>
</div>
</div>
