<div id="favorites" class="dialog_window">
 <ul class="favorite">
  <li><a href="#favorite-1">New Favorite</a></li>
  <?php if($tw->favorites->get() !== False): ?>
   <?php $i=2;foreach($tw->favorites->get() as $fav): ?>
    <li><a href="#favorite-<?php echo $i++;?>"><?php echo $fav->name;?></a></li>
   <?php endforeach;?>
  <?php endif; ?>
 </ul>
 <?php $i=1;$favorite = new favorite(array('name'=>'New Favorite')); include 'views/ajax/favorite.tpl'; ?>
 <?php if($tw->favorites->get() !== False): ?>
  <?php foreach($tw->favorites->get() as $favorite) include 'views/ajax/favorite.tpl'; ?>
 <?php endif; ?>
</div>
