#!/bin/sh

PHP_FILES="atomparser.php lastRSS.php torrentwatch.php rss_dl.php rss_dl_utils.php class.bdecode.php rss_dl.functions.php tor_client.php"
DOC_FILES="TODO CREDITS LICENSE changelog rss_dl.config.orig"
IMAGE_FILES="images/favicon.ico images/*.gif images/*.png"
WEB_FILES="tw-iface.php webtoolkit.contextmenu.js tw-iface.css tw-iface.local.css tw-iface.js $IMAGE_FILES"
RELEASE_FILES="$PHP_FILES $DOC_FILES $WEB_FILES"
RELEASE=torrentwatch-dev-$1

rm -rf release
mkdir -p release

rm *~
cp *installer* release
cp twupload.ftp release
tar -cf release/tw.scripts.tar $RELEASE_FILES

zip -r ../$RELEASE.zip release/*

