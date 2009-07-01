<div id="welcome_dialog" class="dialog_window">
<form id="welcome_form" action="/torrentwatch/index.cgi">
  <div id="welcome-1" class="dialog_window welcome">
    <input type="hidden" name="mode" value="firstrun" />
    <input type="hidden" name="button" value="Add" />
    <h2 class="dialog_heading">Welcome to torrentwatch</h2>
    <label class="item">Torrent Watch is a browser based program designed to bring TV shows from the internet to your living room.</label>
    <label class="item">Torrent Watch accesses feeds from the internet to tell it about newly avaiable shows, and uses TheTVDB.com to pull up detailed information about individual shows and episodes</label>
    <label class="item">You will need to answer a few simple questions to get started.</label>
  </div>

  <div id="welcome-2" class="dialog_window welcome">
    <h2 class="dialog_heading">Choose a Client</h2>
    <label class="item">Torrent Watch can use a variety of clients to download the TV Episodes.  Items with a * are installed by default on the NMT</label>
    <label class="category">Bit Torrent</label>
    <label class="item">Bit Torrent is available to everyone, and as such is very popular.</label>
    <div class="form_radio">
      <input type="radio" name="config[client]" value="btpd" />
      <label class="item">BTPD*</label>
    </div>
    <div class="form_radio">
      <input type="radio" name="config[client]" value="transmission1.22" />
      <label class="item">Transmission 1.22*</label>
    </div>
    <div class="form_radio">
      <input type="radio" name="config[client]" value="transmission1.3x" />
      <label class="item">Transmission &gt;= 1.30</label>
    </div>
    <label class="category">NZB</label>
      <label class="item">NZB Requires you to subscribe to a Newsgroup Server.  Newsgroup servers can be very fast, and will often max out your net connection with a good provider.</label>
    <div class="form_radio">
      <input type="radio" name="config[client]" value="nzbget" />
      <label class="item">NZBGet*</label>
    </div>
    <div class="form_radio">
      <input type="radio" name="config[client]" value="sabnzbd" />
      <label class="item">SabNZBd</label>
    </div>
  </div>

  <div id="welcome-3" class="dialog_window welcome">
    <h2 class="dialog_heading">Choose a Feed</h2>
    <label class="item">Torrent Watch requires a feed from the internet that will point it to the TV Episodes.  Choose a feed from the list or add your own feed from the net.</label>
    <label class="category">Bit Torrent</label>
    <div class="form_radio">
      <input type="radio" name="feed[url]" value="http://tvrss.net/feed/eztv/" /><label class="item">tvRSS.net - EzTV</label>
      <label class="item">The EzTV feed from tvRSS.net</label>
    </div>
    <div class="form_radio">
      <input type="radio" name="feed[url]" value="http://tvrss.net/feed/vtv/" /><label class="item">tvRSS.net - VTV</label>
      <label class="item">The VTV feed from tvRSS.net contains only the most popular tv shows</label>
    </div>
    <label class="category">NZB</label>
    <div class="form_radio">
      <input type="radio" name="feed[url]" value="http://www.tvnzb.com/tvnzb.rss" /><label class="item">TvNZB.com</label>
      <label class="item">TvNZB.com offers a feed of user submitted .nzb files</label>
    </div>
    <div class="form_radio">
      <input type="radio" name="feed[url]" value="http://tvbinz.net/rss.php" /><label class="item">TVBINZ.NET</label>
      <label class="item">TvBinz.net offers an automatically generated feed of files posted to select news groups</label>
    </div>
  </div>
  <div id="welcome-4" class="dialog_window welcome">
    <h2 class="dialog_heading">The Basics</h2>
    <label class="item">Torrentwatch classifys the feed items with various colored leds</label>
    <label class="category">Note</label>
    <label class="item">If you modify or add a favorite in the web interface no new matches will be started until you press the refresh button.</label>
    <ul id='feedItems' class='feedItems'>
      <li class='torrent match_nomatch'>
        <div class='torrent_name'>No Match - Will not be downloaded</div>
        <a class='context_link' href='#'></a><a class='context_link' href='#'></a></li>
      <li class='torrent match_duplicate alt'>
        <div class='torrent_name'>Duplicate Episode - Same episode downloaded previously.  Will not be downloaded.</div>
        <a class='context_link' href='#'></a><a class='context_link' href='#'></a></li>
      <li class='torrent match_old'>
        <div class='torrent_name'>Old Episode - Episode is Older than the most recently downloaded episode. Will not be downloaded.</div>
        <a class='context_link' href='#'></a><a class='context_link' href='#'></a></li>
      <li class='torrent match_cachehit alt'>
        <div class='torrent_name'>Cache Hit - This item was previously downloaded.</div>
        <a class='context_link' href='#'></a><a class='context_link' href='#'></a></li>
      <li class='torrent match_test'>
        <div class='torrent_name'>Test Hit - This item is ready to download.  Press Refresh to start all test hits.</div>
        <a class='context_link' href='#'></a><a class='context_link' href='#'></a></li>
      <li class='torrent match_match alt'>
        <div class='torrent_name'>New Hit - This item has just been started.</div>
        <a class='context_link' href='#'></a><a class='context_link' href='#'></a></li>
    </ul>
  </div>
        
  <div id="welcome-5" class="dialog_window welcome">
    <h2 class="dialog_heading">Favorites Settings</h2>
    <label class="item">Torrent Watch has a few different settings that effect which matches will be downloaded, and which will be passed over.</label>
    <label class="category">
      <input type="checkbox" name="config[onlyNewer]" value="1" checked="checked" />
      Newer Episodes Only
    </label>
    <label class="item">Only download newer episodes.  So if previously S3E10 has been downloaded for a particular favorite, when S2E03 shows up in the feed it will be skipped</label>
    <label class="category">
      <input type="checkbox" name="config[saveFiles]" value="1" />
      Save index files
    </label>
    <label class="item">Save the related .torrent or .nzb file in the download directory</label>
  </div>
  <div id="welcome-6" class="dialog_window welcome">
    <h2 class="dialog_heading">Favorites Settings (cont.)</h2>
  </div>
  <div id="welcome-7" class="dialog_window welcome">
    <h2 class="dialog_heading">Initial Configuration is Complete!</h2>
      <label class="item">To get started click Apply below and you will be presented with the episodes in your chosen feed.</label>
      <label class="item">Right click on any show on the list and add it to your favorites.  Once a show is in your favorites it will automatically download every week</label>
    <label class="category">Note</label>
      <label class="item">Feeds only contain the most recent additions.  For more info please visit your feeds website.</label>
  </div>
</form>
</div>