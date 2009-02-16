<div class="dialog_window" id="history">
  <ul id="historyItems">
    <?php foreach($history as $item): ?>
      <li><?php echo $item['Date'].' - '.$item['Title']; ?></li>
    <?php endforeach; ?>
  </ul>
  <a class="toggleDialog" href="#">Close</a>
  <a class="button" id="clearhistory" href="<?php echo $_SERVER['PHP_SELF'] ?>/ClearHistory">Clear</a>
</div>
