<div class="dialog_window" id="feeds">
  <h2 class="dialog_heading">Feeds</h2>
  <?php if(isset($config_values['Feeds'])): ?>
    <?php foreach($config_values['Feeds'] as $key => $feed): ?>
      <div class="feeditem">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>/updateFeed" class="feedform">
          <input type="hidden" name="idx" value="<?php echo $key; ?>">
          <a class="submitForm button" id="Delete" href="#">Delete</a>
          <label class="item"><?php echo $feed['Name'].': '.$feed['Link']; ?></label>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  <div class="feeditem">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>/updateFeed" class="feedform">
      <a class="submitForm button" id="Add" href="#">Add</a>
      <label class="item">New Feed:</label>
      <input type="text" name="link">
    </form>
  </div>
  <div class="buttonContainer">
    <a class="toggleDialog button" href="#">Close</a>
    <a class="toggleDialog button" href="#configuration">Back</a>
  </div>
</div>
