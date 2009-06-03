<div id="favorites" class="dialog_window">
 <ul class="favorite">
  <li><a href="#favorite-1">New Favorite</a></li>
  <?php if($favorites !== False): ?>
   <?php $i=2;foreach($favorites as $fav): ?>
    <li><a href="#favorite-<?php echo $i++;?>"><?php echo $fav->tvShow->title;?></a></li>
   <?php endforeach;?>
  <?php endif; ?>
 </ul>
 <?php 
   $i=1;
   $favorite = new favoriteTvShow;
   include VIEWPATH.'favorite.tpl';
   if($favorites !== False) {
     foreach($favorites as $favorite) 
       include VIEWPATH.'favorite.tpl';
   }
 ?>
</div>
