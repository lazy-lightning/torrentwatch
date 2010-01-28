<div id="welcome" class="dialog_window welcome">
  <div class="content">
    <h2 class="dialog_heading">Choose a Client</h2>
    <div class="item">
        NMTDVR can use a variety of clients to download the feed items.
        Currently NMTDVR supports feeds that point to either NZB or torrent
        files.  Please select your prefered client for each.  If you dont
        plan on using one or the other set it to the Simple Folder client.
    </div>
    <div class="torrent_client">
      <label class="category">Bit Torrent</label>
      <span class="item">
          Bit Torrent is available to everyone, and as such is very popular.
      </span>
      <?php foreach($availClients[feedItem::TYPE_TORRENT] as $key => $value): ?>
          <div class="form_radio">
              <input type="radio" name="dvrConfig[torClient]" value="<?php echo $key; ?>" />
              <label class="item"><?php echo $value; ?></label>
          </div>
      <?php endforeach; ?>
    </div>
    <div class="nzb_client">
      <label class="category">NZB</label>
      <span class="item">
          NZB Requires you to subscribe to a Newsgroup Server.  Newsgroup
          servers can be very fast, and will often max out your net connection
          with a good provider.
      </span>
      <?php foreach($availClients[feedItem::TYPE_NZB] as $key => $value): ?>
          <div class="form_radio">
              <input type="radio" name="dvrConfig[nzbClient]" value="<?php echo $key; ?>" />
              <label class="item"><?php echo $value; ?></label>
          </div>
      <?php endforeach; ?>
    </div>
    <div class="clear"></div>
    <div class="buttonContainer clearFix">
        <?php echo CHtml::link('Next', array('wizardFeed'), array('class'=>'submitForm button')); ?>
        <?php echo CHtml::link('Back', array('welcome'), array('class'=>'ajaxSubmit button')); ?>
        <a class="toggleDialog button" href="#">Close</a>
    </div>
  </div>
</div>
<?php $this->widget('actionResponseWidget', array('showDialog'=>'#welcome'));
