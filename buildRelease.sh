#!/bin/sh

RELEASE_FILES="atomparser.php lastRSS.php torrentwatch.php rss_dl.php rss_dl.config.orig rss_dl_utils.php tw-iface.php tw-iface.css tw-iface.local.css images CREDITS LICENSE changelog"
RELEASE=`pwd | sed 's/.*folder\///'`

rm -rf release
mkdir -p release

rm *~
rm -rf images/.svn
cp *installer* release
cp twupload.ftp release
tar -cf release/tw.scripts.tar $RELEASE_FILES

zip -r ../$RELEASE.zip release/*

