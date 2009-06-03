<div id="feedItems_container">
 <?php if($feeds): ?>
  <?php $i=0;foreach($feeds as $feed): ?>
   <ul id='feed-<?php echo $i++; ?>' class='feedItems'>
    <li class='header'><?php echo  CHtml::encode(empty($feed->description) ? $feed->title : $feed->description); ?></li>
    <?php include VIEWPATH.'feedItems.tpl' ?>
   </ul>
  <?php endforeach; ?>
 <?php endif; ?>
</div>

