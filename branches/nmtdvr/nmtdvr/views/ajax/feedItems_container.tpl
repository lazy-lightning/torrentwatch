<div id="feedItems_container">
 <?php if($tw->feeds->get()): ?>
  <?php $i=0;foreach($tw->feeds->get() as $feed): ?>
   <ul id='feed-<?php echo $i++; ?>' class='feedItems'>
    <li class='header'><?php echo  empty($feed->description) ? $feed->title : $feed->description; ?></li>
    <?php include 'views/ajax/feedItems.tpl' ?>
   </ul>
  <?php endforeach; ?>
 <?php endif; ?>
</div>

