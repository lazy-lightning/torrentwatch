<div id="history" class="dialog_window">
  <div class="content">
    <ul>
    <?php if(!empty($historyList)): ?>
      <?php $n=0;foreach($historyList as $hItem): ?>
        <li class='<?php echo ++$n%2?'alt':''; ?>'>
          <div class="date"><?php echo date('Y M d h:i a', $hItem->date); ?></div>
          <span><?php echo CHtml::encode($hItem->feedItem_title); ?></span>
          <div class="hItemDetails" id="history-<?php echo $hItem->id; ?>">
            <div class="histFav">Started By: <?php echo CHtml::encode($hItem->favorite_name); ?></div>
            <div class="histFeed">From Feed:<?php echo CHtml::encode($hItem->feed_title); ?></div>
          </div>
        </li>
      <?php endforeach; ?>
    <?php endif; ?>
    </ul>
    <div class="buttonContainer clearFix">
       <?php echo CHtml::link('Clear', array('delete', 'all'=>1), array('class'=>'ajaxSubmit button', 'id'=>'clearHistory')); ?>
       <a class="toggleDialog button" href="#">Close</a>
    </div>
  </div>
</div>