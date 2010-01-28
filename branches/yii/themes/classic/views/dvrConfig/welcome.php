<div id="welcome" class="dialog_window welcome">
  <div class="content clearFix">
    <h2 class="dialog_heading">Welcome to NMTDVR</h2>
    <span class="item">
        NMTDVR is a browser based program designed to bring media from the
        internet to your living room.
    </span>
    <span class="item">
        NMTDVR receives information about available media over the internet
        via an RSS feed provided by the user. TheTVDB.com and IMDB.com are
        used to find out information about the individual media files.
    </span>
    <span class="item">
        You will need to answer a few simple questions to get started.
    </span>
    <div class="buttonContainer clearFix">
      <?php echo CHtml::link('Next', array('wizardClient'), array('class'=>'ajaxSubmit button')); ?>
        <a class="toggleDialog button" href="#configuration">Back</a>
        <a class="toggleDialog button" href="#">Close</a>
    </div>
  </div>
</div>
<?php $this->widget('actionResponseWidget', array('showDialog'=>'#welcome')); ?>

