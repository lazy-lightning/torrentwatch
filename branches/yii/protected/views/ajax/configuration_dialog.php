<div class="dialog_window" id="configuration">
  <div class="content">
    <ul>
     <li><a href="#global_config"><span>Main</span></a></li>
     <li><a href="#torClient"><span>Torrent Client</span></a></li>
     <li><a href="#nzbClient"><span>NZB Client</span></a></li>
     <li><a href="#feeds"><span>Feeds</span></a></li>
    </ul>
    <div id="global_config">
      <?php echo CHtml::beginForm(array('saveConfig'), 'post', array('id'=>'config_form')); ?>
        <h2 class="dialog_heading">Web UI Settings</h2>
        <div id="config_timezone">
          <?php echo CHtml::activeLabel($config, 'timezone', array('class'=>'item')).': '.
                     CHtml::activeTextField($config, 'timezone'); ?>
        </div>
        <div id="config_webui"> <!-- Only used by the javascript, selected via cookie -->
          <label class="item select">Font Size</label>:
          <select name="webui">
            <option value="Small">Small</option>
            <option value="Medium" selected>Medium</option>
            <option value="Large">Large</option>
          </select>
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
        <div id="config_feedItemLifetime">
          <?php echo CHtml::activeLabel($config, 'feedItemLifetime', array('class'=>'item')).': '.
                     CHtml::activeTextField($config, 'feedItemLifetime'); ?>
        </div>
        <div id="config_maxItemsPerFeed">
          <?php echo CHtml::activeLabel($config, 'maxItemsPerFeed', array('class'=>'item')).': '.
                     CHtml::activeTextField($config, 'maxItemsPerFeed'); ?>
        </div>
        <div class="buttonContainer">
          <a class="submitForm button" id="Save" href="#">Save</a>
          <a class='toggleDialog button' href='#'>Close</a>
          <a class='toggleDialog button' href='#welcome1'>Wizard</a>
        </div>
      <?php echo CHtml::endForm(); ?>
    </div>
    <div class="client_config" id="torClient">
      <h2 class="dialog_heading">Torrent Client</h2>
      <?php echo CHtml::dropDownList('dvrConfig[torClient]', $config->torClient, $availClients[feedItem::TYPE_TORRENT]);
            foreach($availClients[feedItem::TYPE_TORRENT] as $client => $title):
              echo CHtml::beginForm(array('saveConfig'), 'post', array('id'=>$client, 'class'=>'config')); ?>
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
        <?php echo CHtml::endForm(); ?>
      <?php endforeach; ?>
    </div>
    <div class="client_config" id="nzbClient">
      <h2 class="dialog_heading">NZB Client</h2>
      <?php echo CHtml::dropDownList('dvrConfig[nzbClient]', $config->nzbClient, $availClients[feedItem::TYPE_NZB]);
            foreach($availClients[feedItem::TYPE_NZB] as $client => $title):
              echo CHtml::beginForm(array('saveConfig'), 'post', array('id'=>$client, 'class'=>'config')); ?>
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
        <?php echo CHtml::endForm(); ?>
      <?php endforeach; ?>
    </div>
    <div id="feeds">
      <h2 class="dialog_heading">Feeds</h2>
      <?php if($feeds): ?>
        <?php foreach($feeds as $feed):
                if($feed->id === '0') continue; // the generic 'all' feeds ?>
        <div class="activeFeed" title="<?php echo CHtml::encode($feed->url); ?>">
          <?php echo CHtml::link('Delete', array('deleteFeed', 'id'=>$feed->id), array('class'=>'button ajaxSubmit', 'id'=>'Delete')); ?>
          <?php echo CHtml::encode($feed->title); ?>
        </div>
       <?php endforeach; ?>
      <?php endif; ?>
      <?php $feed = isset($response['activeFeed-']) ? $response['activeFeed-'] : new feed; ?>
      <div class="activeFeed">
        <?php echo CHtml::beginForm(array('addFeed'), 'post', array('class'=>'feedform')); ?>
          <a class="submitForm button" id="Add" href="#">Add</a>
          <?php echo CHtml::errorSummary($feed); ?>
          <div>
            <label class="item">New Feed</label>
            <?php echo CHtml::dropDownList('feed[downloadType]', $feed->downloadType, array(feedItem::TYPE_NZB=>'NZB', feedItem::TYPE_TORRENT=>'Torrent')); ?>
          </div>
          <div>
            <?php echo CHtml::activeTextField($feed, 'url'); ?>
          </div>
          <div class="buttonContainer">
            <a class='toggleDialog button' href='#'>Close</a>
          </div>
        <?php echo CHtml::endForm(); ?>
      </div>
      <div class='clear'></div>
    </div>
  </div>
</div>
