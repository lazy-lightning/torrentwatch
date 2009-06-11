<div class="dialog_window" id="configuration">
  <ul>
   <li><a href="#global_config"><span>Main</span></a></li>
   <li><a href="#torClient"><span>Torrent Client</span></a></href>
   <li><a href="#nzbClient"><span>NZB Client</span></a></href>
   <li><a href="#feeds"><span>Feeds</span></a></li>
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
  <div class="client_config" id="torClient">
    <?php echo CHtml::activeLabel($config, 'torClient', array('class'=>'item select')).': '.
               CHtml::dropDownList('dvrConfig[torClient]', $config->torClient, $availClients[feedItem::TYPE_TORRENT]);
    foreach($availClients[feedItem::TYPE_TORRENT] as $client => $title): ?>
      <?php echo CHtml::beginForm(array('saveConfig'), 'post', array('id'=>$client, 'class'=>'config')); ?>
        <input type="hidden" name="category" value="<?php echo $client; ?>">
        <input type="hidden" name="type" value="torClient" >
        <?php 
          $clientConfig = $config->$client;
          $htmlAttrs = array('class'=>'item');
          foreach($clientConfig as $key => $value) {
            echo '<div>'.
                   CHtml::activeLabel($clientConfig, $key, $htmlAttrs).': '.
                   CHtml::activeTextField($clientConfig, $key).
                 '</div>';
          }
        ?>
        <div class="buttonContainer">
          <a class="submitForm button" id="Save" href="#">Save</a>
          <a class='toggleDialog button' href='#'>Close</a>
        </div>
      </form>
    <?php endforeach; ?>
  </div>
  <div class="client_config" id="nzbClient">
    <?php echo CHtml::activeLabel($config, 'nzbClient', array('class'=>'item select')).': '.
               CHtml::dropDownList('dvrConfig[nzbClient]', $config->nzbClient, $availClients[feedItem::TYPE_NZB]);
    foreach($availClients[feedItem::TYPE_NZB] as $client => $title): ?>
      <?php echo CHtml::beginForm(array('saveConfig'), 'post', array('id'=>$client, 'class'=>'config')); ?>
        <input type="hidden" name="category" value="<?php echo $client; ?>">
        <input type="hidden" name="type" value="nzbClient" >
        <?php 
          $clientConfig = $config->$client;
          foreach($clientConfig as $key => $value) {
            echo '<div>'.
                   CHtml::activeLabel($clientConfig, $key, $htmlAttrs).': '.
                   CHtml::activeTextField($clientConfig, $key).
                 '</div>';
          }
        ?>
        <div class="buttonContainer">
          <a class="submitForm button" id="Save" href="#">Save</a>
          <a class='toggleDialog button' href='#'>Close</a>
        </div>
      </form>
    <?php endforeach; ?>
  </div>
  <div id="feeds">
    <h2 class="dialog_heading">Feeds</h2>
    <?php if($feeds): ?>
      <?php foreach($feeds as $feed):
              if($feed->id === '0') continue; // the generic 'all' feeds ?>
      <div class="activeFeed" title="<?php echo CHtml::encode($feed->url); ?>">
        <?php echo CHtml::link('Delete', array('deleteFeed', 'id'=>$feed->id), array('class'=>'button', 'id'=>'Delete')); ?>
        <?php echo CHtml::encode($feed->title); ?>
      </div>
     <?php endforeach; ?>
    <?php endif; ?>
    <div class="activeFeed">
      <?php echo CHtml::beginForm(array('addFeed'), 'post', array('class'=>'feedform')); ?>
        <a class="submitForm button" id="Add" href="#">Add</a>
        <label class="item">New Feed</label>
        <input type="text" name="feed[url]" id="feed_url">
        <div class="buttonContainer">
          <a class='toggleDialog button' href='#'>Close</a>
        </div>
      </form>
    </div>
  </div>
</div>
  
