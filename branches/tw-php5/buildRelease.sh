#!/bin/sh

RELEASE_FILES="atomparser.php lastRSS.php torrentwatch.php rss_dl.php rss_dl.config.orig rss_dl_utils.php tw-iface.php tw-iface.css tw-iface.local.css images/*.png CREDITS LICENSE changelog class.bdecode.php rss_dl.functions.php"
RELEASE=torrentwatch-dev-$1

rm -rf release
mkdir -p release

rm *~
cp *installer* release
cp twupload.ftp release
tar -cf release/tw.scripts.tar $RELEASE_FILES

zip -r ../$RELEASE.zip release/*

