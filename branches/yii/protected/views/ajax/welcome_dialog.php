<div id="welcome_dialog">
    <?php echo CHtml::beginForm(array('wizard'), 'post', array('id'=>'welcome_form')); ?>
    <div id="welcome1" class="dialog_window welcome">
        <h2 class="dialog_heading">Welcome to NMTDVR</h2>
        <span class="item">
            NMTDVR is a browser based program designed to bring media from the
            internet to your living room.
        </span>
        <span class="item">
            NMTDVR receives information about available media over the internet
            via an RSS feed provided by the user. TheTVDB.com and IMDB.com are
            used to find out information about the individual media files.
        </span>
        <span class="item">
            You will need to answer a few simple questions to get started.
        </span>
    </div>

    <div id="welcome2" class="dialog_window welcome">
        <h2 class="dialog_heading">Choose a Client</h2>
        <span class="item">
            NMTDVR can use a variety of clients to download the feed items.
            Currently NMTDVR supports feeds that point to either NZB or torrent
            files.  Please select your prefered client for each.  If you dont
            plan on using one or the other set it to the Simple Folder client.
        </span>
        <label class="category">Bit Torrent</label>
        <span class="item">
            Bit Torrent is available to everyone, and as such is very popular.
        </span>
        <?php foreach($availClients[feedItem::TYPE_TORRENT] as $key => $value): ?>
            <div class="form_radio">
                <input type="radio" name="config[torClient]" value="<?php echo $key; ?>" />
                <label class="item"><?php echo $value; ?></label>
            </div>
        <?php endforeach; ?>
        <label class="category">NZB</label>
        <span class="item">
            NZB Requires you to subscribe to a Newsgroup Server.  Newsgroup
            servers can be very fast, and will often max out your net connection
            with a good provider.
        </span>
        <?php foreach($availClients[feedItem::TYPE_NZB] as $key => $value): ?>
            <div class="form_radio">
                <input type="radio" name="config[nzbClient]" value="<?php echo $key; ?>" />
                <label class="item"><?php echo $value; ?></label>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="welcome3" class="dialog_window welcome">
        <h2 class="dialog_heading">Choose a Feed</h2>
        <span class="item">
            NMTDVR requires an RSS feed from the internet that will point it to
            newly available media.  Choose a feed from the list or add your own.
        </span>
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
    </div>
    <div id="welcome4" class="dialog_window welcome">
        <h2 class="dialog_heading">The Basics</h2>
        <label class="item">
            NMTDVR classifys the feed items with various colored leds
        </label>
        <label class="category">Note</label>
        <ul id='feedItems' class='feedItems'>
            <li class='torrent match_nomatch'>
                <div class='torrent_name'>No Match - Will not be downloaded</div>
            </li>
            <li class='torrent match_duplicate alt'>
                <div class='torrent_name'>Duplicate Episode - Same episode downloaded previously.  Will not be downloaded.</div>
            </li>
            <li class='torrent match_old'>
                <div class='torrent_name'>Old Episode - Episode is Older than the most recently downloaded episode. Will not be downloaded.</div>
            </li>
            <li class='torrent match_auto alt'>
                <div class='torrent_name'>Automatic DL - This item was automatically downloaded.</div>
             </li>
            <li class='torrent match_test'>
                <div class='torrent_name'>Test Hit - This item is ready to download.  Press Refresh to start all test hits.</div>
            </li>
            <li class='torrent match_match alt'>
                <div class='torrent_name'>New Hit - This item has just been started.</div>
            </li>
            <li class="match_failed">
                <div class="torrent_name">Failed Download - This item has failed to start.</div>
            </li>
            <li class="match_new">
                <div class="torrent_name">New Item - This item has yet to be fully processed.</div>
            </li>
            <li class="torrent match_manual">
                <div class="torrent_name">Manual Download - This item was started from the user interface.</div>
            </li>
            <li class="torrent match_queue">
                <div class="torrent_name">Queued for User - This item has been marked by a favorite to queue up.</div>
            </li>
        </ul>
    </div>

    <div id="welcome5" class="dialog_window welcome">
        <h2 class="dialog_heading">Settings</h2>
        <label class="item">
            NMTDVR has a few different settings that effect its behavior.
        </label>
        <label class="category">
            <?php echo CHtml::activeCheckBox($config, 'saveFile').
                       CHtml::activeLabel($config, 'saveFile'); ?>
        </label>
        <label class="item">
            Save the related .torrent or .nzb file in the download directory.
        </label>
        <label class="category">
            <?php echo CHtml::activeCheckBox($config, 'downloadDir').
                       CHtml::activeLabel($config, 'downloadDir'); ?>
        </label>
        <label class="item">
            The directory for all feed items to be downloaded to.
        </label>
        <label class="category">
            <?php echo CHtml::activeCheckBox($config, 'feedItemLifetime').
                       CHtml::activeLabel($config, 'feedItemLifetime'); ?>
        </label>
        <label class="item">
            The time in days that a feed item will remain in the local database
        </label>
    </div>
    <div id="welcome6" class="dialog_window welcome">
        <h2 class="dialog_heading">Initial Configuration is Complete!</h2>
        <label class="item">
            To get started click Apply below and you will be presented with the
            feed items from your chosen feed.
        </label>
        <label class="item">
            Right click on any show on the list and add it to your favorites.
            Once a show is in your favorites it will automatically download
            every week.
        </label>
        <label class="category">Note</label>
        <label class="item">
            Feeds only contain the most recent additions.  For more info please
            visit your feeds website.
        </label>
    </div>
    <?php echo CHtml::endForm(); ?>
</div>
