<div class="dialog_window" id="configuration">
  <h2 class="dialog_heading">Configuration</h2>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>/setGlobals" id="config_form">
    <label class="category">Client Settings</label>
    <div id="config_torrentclient">
      <label class="item select" title="Which torrent client to use">Client:</label>
      <select name="client" id="client">
        <option value="btpd" <?php echo $btpd ?>>BTPD</option>
        <option value="transmission1.22" <?php echo $trans122; ?>>Transmission 1.22</option>
        <option value="transmission1.3x" <?php echo $trans13x; ?>>Transmission &gt;= 1.30</option>
        <option value="nzbget" <?php echo $nzbget; ?>>NZBGet</option>
        <option value="sabnzbd" <?php echo $sabnzbd; ?>>SabNZBd</option>
        <option value="folder" <?php echo $folderclient; ?>>Simple Folder</option>
      </select>
    </div>
    <div id="config_folderclient">
      <label class="item">File Extension</label>
      <input type="text" name="extension" value="<?php echo $config_values['Settings']['Extension']; ?>">
    </div>
    <div id="config_downloaddir" title="Default directory to start items in">
      <label class="item textinput">Download Directory:</label>
      <input type="text" name="downdir" value="<?php echo $config_values['Settings']['Download Dir']; ?>">
    </div>
    <label class="category" id="torrent_settings">Torrent Settings</label>
    <div id="config_watchdir">
      <label class="item textinput" title="Directory to look for new .torrents">Watch Directory:</label>
      <input type="text" name="watchdir" value="<?php echo $config_values['Settings']['Watch Dir']; ?>">
    </div>
    <div id="config_savetorrent">
      <input type="checkbox" name="savetorrents" value=1 <?php echo $savetorrent; ?>>
      <label class="item checkbox" title="Save index(.torrent or .nzb file) to download directory">Save index files</label>
    </div>
    <div id="config_deepdir">
      <label class="item select" title="Save downloads in multi-directory structure">Deep Directories:</label>
      <select name="deepdir">
        <option value="Full" <?php echo $deepfull; ?>>Full Name</option>
        <option value="Title" <?php echo $deeptitle; ?>>Show Title</option>
        <option value="0" <?php echo $deepoff; ?>>Off</option>
      </select>
    </div>
    <label class="category">Favorites Settings</label>
    <div id="config_verifyepisodes" title="Try not to download duplicate episodes">
      <input type="checkbox" name="verifyepisodes" value=1 <?php echo $verifyepisode; ?>>
      <label class="item checkbox">Verify Episodes</label>
    </div>
    <div id="config_onlynewer" title="Only download episodes newer than the last">
      <input type="checkbox" name="onlynewer" value=1 <?php echo $onlynewer; ?>>
      <label class="item checkbox">Newer Episodes Only</label>
    </div>
    <div id="config_matchstyle">
      <label class="item select" title="Type of filter to use">Matching Style:</label>
      <select name="matchstyle">
        <option value="regexp" <?php echo $matchregexp; ?>>RegExp</option>
        <option value="glob" <?php echo $matchglob; ?>>Glob</option>
        <option value="simple" <?php echo $matchsimple; ?>>Simple</option>
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

