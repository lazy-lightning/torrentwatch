      <?php echo CHtml::beginForm(array('/dvrConfig/globals'), 'post', array('id'=>'global_config'));
      echo CHtml::errorSummary($config); 
      if($saved)
        echo "<div class='saved'>Saved</div>";
      ?>
        <h2 class="dialog_heading">Web UI Settings</h2>
        <div id="config_timezone">
          <?php 
            echo CHtml::activeLabel($config, 'timezone', array('class'=>'item')).': ';
            foreach(DateTimeZone::listIdentifiers() as $key => $value) {
              if($key > 0) // skip first element: localtime
                $zones[$value] = $value;
            }
            echo CHtml::dropDownList('dvrConfig[timezone]', $config->timezone, $zones); 
          ?>
        </div>
        <div id="config_webItemsPerLoad">
          <?php echo CHtml::activeLabel($config, 'webItemsPerLoad', array('class'=>'item', 'title'=>'The number of items that will be displayed in the tv episodes/movies/others tabs')).': '.
                     CHtml::activeTextField($config, 'webItemsPerLoad', array('title'=>'The number of items that will be displayed in the tv episodes/movies/others tabs')); ?>
        </div>
        <h2 class="dialog_heading">Client Settings</h2>
        <div id="config_downloaddir" title="Default directory to start items in">
          <?php echo CHtml::activeLabel($config, 'downloadDir', array('class'=>'item')).': '.
                     CHtml::activeTextField($config, 'downloadDir'); ?>
        </div>
        <div id="config_savetorrent">
           <?php echo CHtml::activeLabel($config, 'saveFile', array('class'=>'item checkbox', 'title'=>'Save the related .torrent or .nzb file for started items to their download directory')).': '.
                      CHtml::activeCheckBox($config, 'saveFile', array('title'=>'Save the related .torrent or .nzb file for started items to their download directory')); ?>
        </div>
        <h2 class="dialog_heading">Database Settings</h2>
        <div id="config_maxItemsPerFeed">
          <?php echo CHtml::activeLabel($config, 'maxItemsPerFeed', array('class'=>'item', 'title'=>'The maximum number of feed items to be stored in the database per feed')).': '.
                     CHtml::activeTextField($config, 'maxItemsPerFeed', array('title'=>'The maximum number of feed items to be stored in the database per feed')); ?>
        </div>
        <div class="buttonContainer">
          <a class="submitForm button" id="Save" href="#">Save</a>
          <a class='toggleDialog button' href='#'>Close</a>
          <?php echo CHtml::link('Wizard', array('welcome', '#'=>'welcome'), array('class'=>'toggleDialog button')); ?>
        </div>
      <?php echo CHtml::endForm(); ?>
      <script type='text/javascript'>
        $('#dvrConfig_downloadDir').autocomplete('nmtdvr.php', {
            matchCase: true,
            extraParams: { f: 'autocompleteDirectory' } 
        });
      </script>
