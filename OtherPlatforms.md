Torrentwatch was created for the NMT platform, but versions after 0.7.7 should work on any unix style system(freebsd, linux, etc.).  On other systems the only valid client will be the transmission >= 1.30 client.

When you download the torrentwatch package, only one file in the package is important, tw.scripts.tar.  This tar contains the torrentwatch scripts.  You will want to extract it to a directory that is accessable from a http server and has CGI enabled.  You will also want to have php-cgi installed.  By default torrentwatch looks to /usr/bin/php-cgi for its interpreter.  If necessary change this in the first line of rss\_dl.php and index.cgi

Once the script is in place, load up index.cgi in your web browser and make sure it loads up.  After the first load it should have created a directory ~/.torrents (from whichever user http runs cgi as).  This is where torrentwatch will store configuration, cache, and history data.  You can change it in php/platform.c the platform\_getUserRoot() function.

Once the web interface is working properly you will want to setup a cron job to run rss\_dl.php every hour(or however often you want to check for updates.  Note that torrentwatch will internally cache feeds for 50 minutes).  An example cron entry would be


`30 * * * * /var/www/torrentwatch/rss_dl.php -D`

The begining tells cron to run the script at 30 minutes past the hour every hour.  The -D switch tells rss\_dl.php to also check the watch folder.