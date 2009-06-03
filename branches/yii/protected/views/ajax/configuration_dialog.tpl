<div class="dialog_window" id="configuration">
  <h2 class="dialog_heading">Configuration</h2>
  <?php echo CHtml::beginForm(array('saveConfig'), 'post', array('id'=>'config_form')); ?>
    <label class="category">Web UI Settings</label>
    <div id="config_webui"> <!-- Only used by the javascript, selected via cookie -->
      <label class="item select">Font Size:</label>
      <select name="webui" id="config_webui">
        <option value="Small">Small</option>
        <option value="Medium" selected>Medium</option>
        <option value="Large">Large</option>
      </select>
    </div>
    <label class="category">Client Settings</label>
    <div id="config_torrentclient">
      <?php echo CHtml::activeLabel($config, 'client', array('class'=>'item select')).': '.
                 CHtml::dropDownList('dvrConfig[client]', $config->client, $availClients); ?>
    </div>
    <div id="config_folderclient">
      <?php echo CHtml::activeLabel($config, 'fileExtension',array('class'=>'item')).': '.
                 CHtml::activeTextField($config, 'fileExtension'); ?>
    </div>
    <div id="config_downloaddir" title="Default directory to start items in">
      <?php echo CHtml::activeLabel($config, 'downloadDir', array('class'=>'item')).': '.
                 CHtml::activeTextField($config, 'downloadDir'); ?>
    </div>
    <label class="category" id="torrent_settings">Torrent Settings</label>
    <div id="config_watchdir">
      <?php echo CHtml::activeLabel($config, 'watchDir', array('class'=>'item textinput')).': '.
                 CHtml::activeTextField($config, 'watchDir'); ?>
    </div>
    <div id="config_savetorrent">
      <?php echo CHtml::activeCheckBox($config, 'saveFile').' '.
                 CHtml::activeLabel($config, 'saveFile', array('class'=>'item checkbox')); ?>
    </div>
    <label class="category">Favorites Settings</label>
    <div id="config_seedratio">
      <?php echo CHtml::activeLabel($config, 'seedRatio', array('class'=>'item textinput')).': '.
                 CHtml::activeTextField($config, 'seedRatio'); ?>
    </div>
    <div id="config_matchstyle">
      <?php echo CHtml::activeLabel($config, 'matchStyle', array('class'=>'item select')).': '.
                 CHtml::dropDownList('dvrConfig[matchStyle]', $config->matchStyle, array('regexp', 'glob', 'simple')); ?>
    </div>
    <div class="buttonContainer">
      <a class="submitForm button" id="Save" href="#">Save</a>
      <a class='toggleDialog button' href='#'>Close</a>
      <a class='toggleDialog button' href='#feeds'>Feeds</a>
      <a class='toggleDialog button' href='#welcome1'>Wizard</a>
    </div>
  </form>
</div>

