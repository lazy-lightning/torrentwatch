<div id="favorites" class="dialog_window">
 <ul class="favTypes">
  <li><a href="#favoriteTvShows"><span>TV Shows</span></a></li>
  <li><a href="#favoriteMovies"><span>Movies</span></a></li>
  <li><a href="#favoriteStrings"><span>Strings</span></a></li>
 </ul>
 <div id="favoriteTvShows">
  <ul class="favorite">
   <li><a href="#favoriteTvShows-1">New Favorite</a></li>
   <?php if($favorites !== False): ?>
    <?php $i=2;foreach($favoriteTvShows as $fav): ?>
     <li><a href="#favoriteTvShows-<?php echo $i++;?>"><?php echo $fav->tvShow->title;?></a></li>
    <?php endforeach;?>
   <?php endif; ?>
  </ul>
  <?php 
    $i=1;
    $favorite = new favoriteTvShow;
    include VIEWPATH.'favoriteTvShow.tpl';
    if($favoriteTvShows !== null) {
      foreach($favoriteTvShows as $favorite) 
        include VIEWPATH.'favoriteTvShow.tpl';
    }
  ?>
  <div class="clear"></div>
 </div>
 <div id="favoriteMovies">
  <ul class="favorite">
   <li><a href="#favoriteMovies-1">New Favorite</a></li>
   <?php if($favoriteMovies !== null): ?>
    <?php $i=2;foreach($favoriteMovies as $fav): ?>
     <li><a href="#favoriteMovies-<?php echo $i++;?>"><?php echo $fav->name;?></a></li>
    <?php endforeach;?>
   <?php endif; ?>
  </ul>
  <?php 
    $i=1;
    $favorite = new favoriteMovies;
    include VIEWPATH.'favoriteMovie.tpl';
    if($favoriteMovies !== null) {
      foreach($favoriteMovies as $favorite) 
        include VIEWPATH.'favoriteMovie.tpl';
    }
  ?>
  <div class="clear"></div>
 </div>
 <div id="favoriteStrings">
 </div>
</div>
