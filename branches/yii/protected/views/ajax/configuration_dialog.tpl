<div class="dialog_window" id="configuration">
  <ul>
   <li><a href="#global_config"><span>Main</span></a></li>
   <li><a href="#torClient_config"><span>Torrent Client</span></a></href>
   <li><a href="#nzbClient_config"><span>NZB Client</span></a></href>
   <li><a href="#feed_config"><span>Feeds</span></a></li>
  </ul>
  <div id="global_config">
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
      <div class="buttonContainer">
        <a class="submitForm button" id="Save" href="#">Save</a>
        <a class='toggleDialog button' href='#'>Close</a>
        <a class='toggleDialog button' href='#welcome1'>Wizard</a>
      </div>
    </form>
  </div>
  <div id="torClient_config">
    <?php echo CHtml::activeLabel($config, 'torClient', array('class'=>'item select')).': '.
               CHtml::dropDownList('dvrConfig[torClient]', $config->torClient, $availClients[feedItem::TYPE_TORRENT]);
    foreach($availClients[feedItem::TYPE_TORRENT] as $client => $title): ?>
      <div id="<?php echo $client ?>">
        <h3><?php echo $client;  ?></h3>
        <?php 
          $clientConfig = $config->$client;
          foreach($clientConfig as $key => $value) {
            echo '<div id="config_'.$client.'_'.$key.'">'.
                 CHtml::activeLabel($clientConfig, $key).': '.
                 CHtml::activeTextField($clientConfig, $key).
                 '</div>';
          }
        ?>
      </div>
    <?php endforeach; ?>
  </div>
  <div id="nzbClient_config">
    <?php echo CHtml::activeLabel($config, 'nzbClient', array('class'=>'item select')).': '.
               CHtml::dropDownList('dvrConfig[nzbClient]', $config->nzbClient, $availClients[feedItem::TYPE_NZB]);
    foreach($availClients[feedItem::TYPE_NZB] as $client => $title): ?>
      <div id="<?php echo $client ?>">
        <h3><?php echo $client;  ?></h3>
        <?php 
          $clientConfig = $config->$client;
          foreach($clientConfig as $key => $value) {
            echo '<div id="config_'.$client.'_'.$key.'">'.
                 CHtml::activeLabel($clientConfig, $key).': '.
                 CHtml::activeTextField($clientConfig, $key).
                 '</div>';
          }
        ?>
      </div>
    <?php endforeach; ?>
  </div>
  <div id="feed_config">
    <h2 class="dialog_heading">Feeds</h2>
    <?php if($feeds): ?>
      <?php foreach($feeds as $feed):
              if($feed->id === '0') continue; // the generic 'all' feeds ?>
      <div class="feeditem" title="<?php echo CHtml::encode($feed->url); ?>">
        <?php echo CHtml::link('Delete', array('deleteFeed', 'id'=>$feed->id), array('class'=>'button', 'id'=>'Delete')); ?>
        <?php echo CHtml::encode($feed->title); ?>
      </div>
     <?php endforeach; ?>
    <?php endif; ?>
    <div class="feeditem">
      <?php echo CHtml::beginForm(array('addFeed'), 'post', array('class'=>'feedform')); ?>
        <a class="submitForm button" id="Add" href="#">Add</a>
        <label class="item">New Feed</label>
        <input type="text" name="feed[url]" id="feed_url">
      </form>
    </div>
  </div>
</div>
  
