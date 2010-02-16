      <?php echo CHtml::beginForm(array('/dvrConfig/globals'), 'post', array('id'=>'global_config'));
      echo CHtml::errorSummary($config); 
      if($saved)
        echo "<div class='saved'>Saved</div>";
      ?>
        <h2 class="dialog_heading">Web UI Settings</h2>
        <div id="config_timezone">
          <?php echo CHtml::activeLabel($config, 'timezone', array('class'=>'item')).': '.
                     CHtml::activeTextField($config, 'timezone'); ?>
        </div>
        <div id="config_webItemsPerLoad">
          <?php echo CHtml::activeLabel($config, 'webItemsPerLoad', array('class'=>'item')).': '.
                     CHtml::activeTextField($config, 'webItemsPerLoad'); ?>
        </div>
        <h2 class="dialog_heading">Client Settings</h2>
        <div id="config_downloaddir" title="Default directory to start items in">
          <?php echo CHtml::activeLabel($config, 'downloadDir', array('class'=>'item')).': '.
                     CHtml::activeTextField($config, 'downloadDir'); ?>
        </div>
        <div id="config_savetorrent">
           <?php echo CHtml::activeLabel($config, 'saveFile', array('class'=>'item checkbox')).': '.
                      CHtml::activeCheckBox($config, 'saveFile'); ?>
        </div>
        <h2 class="dialog_heading">Database Settings</h2>
        <div id="config_maxItemsPerFeed">
          <?php echo CHtml::activeLabel($config, 'maxItemsPerFeed', array('class'=>'item')).': '.
                     CHtml::activeTextField($config, 'maxItemsPerFeed'); ?>
        </div>
        <div class="buttonContainer">
          <a class="submitForm button" id="Save" href="#">Save</a>
          <a class='toggleDialog button' href='#'>Close</a>
          <?php echo CHtml::link('Wizard', array('welcome', '#'=>'welcome'), array('class'=>'toggleDialog button')); ?>
        </div>
      <?php echo CHtml::endForm(); ?>
