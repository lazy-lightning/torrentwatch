<?php $h = False; ?>
<div id="history" class="dialog_window">
 <?php if(!empty($h)): ?>
  <ul>
   <?php foreach(array_reverse($h) as $hItem): ?>
    <li>
      <div class="date"><?php echo date('Y M d h:i a', $hItem->date); ?></div>
      <a href="#histItem<?php echo $hItem->id; ?>">
          <?php echo $hItem->title; ?>
      </a>
      <div class="hItemDetails" id="histItem<?php echo $hItem->id; ?>">
        <?php if(!empty($hItem->shortTitle)): ?>
          <div class="histShort">
            <?php echo sprintf('%s S%02dE%02d', $hItem->shortTitle, $hItem->season, $hItem->episode); ?>
          </div>
        <?php endif; ?>  
        <div class="histFav"><?php echo $hItem->favName; ?></div>
        <div class="histFeed"><?php echo $hItem->feedTitle; ?></div>
      </div>
    </li>
   <?php endforeach; ?>
  </ul>
 <?php endif; ?>
</div>
