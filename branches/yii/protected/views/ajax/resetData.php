<div class="dialog_window" id="reset_data"> 
  <h2 class="dialog heading">Reset Stored Data</h2>
  <ul>
    <li class="clear">
      <p>Delete all known data and update feeds from the internet</p>
      <?php echo CHtml::link('All', array('resetData', 'type'=>'all'), array('class'=>'button ajaxSubmit')); ?>
    </li>

    <li class="clear">
      <p>Reset all media types(episodes, movies, etc) to new status</p>
      <?php echo CHtml::link('Media', array('resetData', 'type'=>'media'), array('class'=>'button ajaxSubmit')); ?>
    </li>

    <li class="clear">
      <p>Reset all feed items to unmatched status</p>
      <?php echo CHtml::link('Items', array('resetData', 'type'=>'feedItems'), array('class'=>'button ajaxSubmit')); ?>
    </li>
  </ul>
  <div class="buttonContainer clear">
    <a class="toggleDialog button" href="#">Close</a>
  </div>
</div> 
