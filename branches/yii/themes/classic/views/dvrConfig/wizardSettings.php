<div id="welcomeSettings" class="dialog_window welcome">
  <div class="content clearFix">
    <?php echo CHtml::beginForm(array('wizardSettings'), 'post'); ?>
    <h2 class="dialog_heading">Settings</h2>
    <?php echo CHtml::errorSummary($config); ?>
    <div>
        <p>Save the related .torrent or .nzb file in the download directory.</p>
        <?php echo CHtml::activeCheckBox($config, 'saveFile').
                   CHtml::activeLabel($config, 'saveFile'); ?>
    </div>
    <div>
        <p>The directory for all downloads to start in.</p>
        <?php echo CHtml::activeLabel($config, 'downloadDir').':'.
                   CHtml::activeTextField($config, 'downloadDir'); ?>
    </div>
    <div>
        <p>Select a location in your timezone</p>
        <?php foreach(DateTimeZone::listIdentifiers() as $key => $value) { 
                if($key > 0) // skip first element: localtime
                  $zones[$value] = $value; 
              } 
              echo CHtml::dropDownList('dvrConfig[timezone]', $config->timezone, $zones); ?>
    </div>
    <div>
        <p>Periodicaly check for new versions of NMTDVR</p>
        <?php echo CHtml::activeCheckBox($config, 'checkNewVersion').':'.
                   CHtml::activeLabel($config, 'checkNewVersion'); ?>
    </div>
    <div>
        <p>Submit anonymous usage logs to help focus development</p>
        <?php echo CHtml::activeCheckBox($config, 'submitUsageLogs').':'.
                   CHtml::activeLabel($config, 'submitUsageLogs'); ?>
    </div>
    <div class="buttonContainer clearFix">
        <a class='submitForm button' href='#'>Next</a>
        <?php echo CHtml::link('Back', array('wizardFeed'), array('class'=>'ajaxSubmit button')); ?>
        <a class="toggleDialog button" href="#">Close</a>
    </div>
    <?php echo CHtml::endForm(); ?>
  </div>
</div>
<script type='text/javascript'>
  $('#dvrConfig_downloadDir').autocomplete('nmtdvr.php', {
      matchCase: true,
      extraParams: { f: 'autocompleteDirectory' } 
  });
</script>
<?php $this->widget('actionResponseWidget', array(
      'showDialog'=>'#welcomeSettings',
      // this actually belongs with wizardFeed, but quick and easy because this is next page
      'resetFeedItems'=>'true',
));
