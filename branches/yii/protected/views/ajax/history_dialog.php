<?php $h = False; ?>
<div id="history" class="dialog_window">
 <ul>
  <?php if(!empty($history)): ?>
   <?php $n=0;foreach($history as $hItem): ?>
    <li class='<?php echo ++$n%2?'alt':''; ?>'>
      <div class="date"><?php echo date('Y M d h:i a', $hItem->date); ?></div>
      <a href="#histItem<?php echo $hItem->id; ?>">
          <?php echo $hItem->feedItem_title; ?>
      </a>
      <div class="hItemDetails" id="histItem<?php echo $hItem->id; ?>">
        <div class="histFav"><?php echo $hItem->favorite_name; ?></div>
        <div class="histFeed"><?php echo $hItem->feed_title; ?></div>
      </div>
    </li>
   <?php endforeach; ?>
  <?php endif; ?>
 </ul>
 <div class="buttonContainer">
   <?php echo CHtml::link('Clear', array('clearHistory'), array('class'=>'historySubmit button', 'id'=>'clearHistory')); ?>
   <a class="toggleDialog button" href="#">Close</a>
 </div>
</div>
