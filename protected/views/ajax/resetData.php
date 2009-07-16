<div class="dialog_window" id="reset_data"> 
  <div class="content">
    <h2 class="dialog_heading">Reset Stored Data</h2>
    <ul>
      <li class="clearFix">
        <?php echo CHtml::link('All', array('resetData', 'type'=>'all'), array('class'=>'button ajaxSubmit')); ?>
        <p>Delete all known data and update feeds from the internet</p>
      </li>
  
      <li class="clearFix">
        <?php echo CHtml::link('Media', array('resetData', 'type'=>'media'), array('class'=>'button ajaxSubmit')); ?>
        <p>Reset all media types(episodes, movies, etc) to new status</p>
      </li>
  
      <li class="clearFix">
        <?php echo CHtml::link('Items', array('resetData', 'type'=>'feedItems'), array('class'=>'button ajaxSubmit')); ?>
        <p>Reset all feed items to unmatched status</p>
      </li>
    </ul>
    <div class="buttonContainer clearFix">
      <a class="toggleDialog button" href="#">Close</a>
    </div>
  </div>
</div> 
