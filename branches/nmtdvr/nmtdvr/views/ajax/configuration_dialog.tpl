<?php require('formAssist.php'); $c = TwConfig::getInstance(); ?>
<div class="dialog_window" id="configuration">
  <h2 class="dialog_heading">Configuration</h2>
  <form action="<?php echo $baseuri; ?>/ajax/setGlobals" id="config_form">
    <label class="category">Web UI Settings</label>
    <div id="config_webui">
      <label class="item select">Font Size:</label>
      <select name="webui" id="config_webui">
        <option value="Small">Small</option>
        <option value="Medium" selected>Medium</option>
        <option value="Large">Large</option>
      </select>
    </div>
    <label class="category">Client Settings</label>
    <div id="config_torrentclient">
      <label class="item select" title="Which torrent client to use">Client:</label>
      <select name="client" id="client">
        <?php form::setItem($c->client); ?>
        <option value="btpd" <?php form::isSelected('btpd');  ?>>BTPD</option>
        <option value="trans1.22" <?php form::isSelected('trans1.22'); ?>>Transmission 1.22</option>
        <option value="transRPC" <?php form::isSelected('transRPC'); ?>>Transmission &gt;= 1.30</option>
        <option value="nzbget" <?php form::isSelected('nzbget'); ?>>NZBGet</option>
        <option value="sabnzbd" <?php form::isSelected('sabnzbd'); ?>>SabNZBd</option>
        <option value="folder" <?php form::isSelected('folder'); ?>>Simple Folder</option>
      </select>
    </div>
    <?php // Experimental ?>
    <?php foreach($c->clientOptions as $key => $options): ?>
      <fieldset class="client_options" id="client<?php echo $key; ?>">
        <legend><?php echo $key; ?></legend>
        <?php foreach($options as $left => $right): ?>
          <div class="client_option">
            <label class="item"><?php echo ucwords($left); ?></label>
            <input type="text" name="client_<?php echo $key.'_'.$left; ?>" value="<?php echo $right; ?>">
          </div>
        <?php endforeach; ?>
      </fieldset>
    <?php endforeach; ?>
    <div id="config_folderclient">
      <label class="item">File Extension</label>
      <input type="text" name="extension" value="<?php echo $c->fileExtension; ?>">
    </div>
    <div id="config_downloaddir" title="Default directory to start items in">
      <label class="item textinput">Download Directory:</label>
      <input type="text" name="downdir" value="<?php echo $c->downloadDir; ?>">
    </div>
    <label class="category" id="torrent_settings">Torrent Settings</label>
    <div id="config_watchdir">
      <label class="item textinput" title="Directory to look for new .torrents">Watch Directory:</label>
      <input type="text" name="watchdir" value="<?php echo $c->watchDir; ?>">
    </div>
    <div id="config_savetorrent">
      <input type="checkbox" name="savetorrents" value=1 <?php echo form::checked($c->saveTorrents); ?>>
      <label class="item checkbox" title="Save index(.torrent or .nzb file) to download directory">Save index files</label>
    </div>
    <label class="category">Favorites Settings</label>
    <div id="config_verifyepisodes" title="Try not to download duplicate episodes">
      <input type="checkbox" name="verifyepisodes" value=1 <?php echo form::checked($c->verifyEpisode); ?>>
      <label class="item checkbox">Verify Episodes</label>
    </div>
    <div id="config_onlynewer" title="Only download episodes newer than the last">
      <input type="checkbox" name="onlynewer" value=1 <?php echo form::checked($c->onlyNewer); ?>>
      <label class="item checkbox">Newer Episodes Only</label>
    </div>
    <div id="config_matchstyle">
      <label class="item select" title="Type of filter to use">Matching Style:</label>
      <select name="matchstyle">
        <?php form::setItem($c->matchStyle); ?>
        <option value="regexp" <?php form::isSelected('regexp'); ?>>RegExp</option>
        <option value="glob" <?php form::isSelected('glob'); ?>>Glob</option>
        <option value="simple" <?php form::isSelected('simple');  ?>>Simple</option>
      </select>
    </div>
    <div class="buttonContainer">
      <a class="submitForm button" id="Save" href="#">Save</a>
      <a class='toggleDialog button' href='#'>Close</a>
      <a class='toggleDialog button' href='#feeds'>Feeds</a>
      <a class='toggleDialog button' href='#welcome1'>Wizard</a>
    </div>
  </form>
</div>

