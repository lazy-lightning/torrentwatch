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
        if(isset($response[$class]))
        {
          $favorite = $response[$class];
          include VIEWPATH.$class.'.php';
        }
      ?>
    </div>
   <?php endforeach; ?>
  </div>
 </div>
</div>
