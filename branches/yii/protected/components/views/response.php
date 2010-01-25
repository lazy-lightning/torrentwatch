<?php if($dialog): ?>
  <div class="dialog_window" id="actionResponse">
    <div class="content">
      <h2 class="dialog_heading"><?php echo $dialog['header']; ?></h2>
      <p><?php echo isset($dialog['content']) ? $dialog['content'] : ''; ?></p>
    </div>
  </div>
<?php endif; ?>
<?php if($jScript): ?>
  <script language="javascript" type="text/javascript">
    <?php echo $jScript; ?>;
  </script>
<?php endif; ?>

