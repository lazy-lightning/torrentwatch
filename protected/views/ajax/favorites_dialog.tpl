<div id="favorites" class="dialog_window">
 <ul class="favTypes">
  <li><a href="#favoriteTvShows"><span>TV Shows</span></a></li>
  <li><a href="#favoriteMovies"><span>Movies</span></a></li>
  <li><a href="#favoriteStrings"><span>Strings</span></a></li>
 </ul>
 <?php foreach(array('favoriteTvShows', 'favoriteMovies', 'favoriteStrings') as $favType):
         $class = substr($favType, 0, -1); ?>
   <div id="<?php echo $favType; ?>">
    <ul class="favorite">
     <li><a href="#<?php echo $favType; ?>-1">New Favorite</a></li>
     <?php if($$favType !== null): 
            $i=2;foreach($$favType as $fav): ?>
       <li><a href="#<?php echo $favType.'-'.$i++.'">'.$fav->name;?></a></li>
      <?php endforeach;
           endif; ?>
    </ul>
    <?php 
      $i=1;
      $favorite = new $class;
      include VIEWPATH.$class.'.tpl';
      // $$ points to the variable named in the named variable
      // so $favoriteTvShows, or whatever
      if($$favType !== null) {
        foreach($$favType as $favorite) 
          include VIEWPATH.$class.'.tpl';
      }
    ?>
    <div class="clear"></div>
   </div>
 <?php endforeach; ?>
</div>
