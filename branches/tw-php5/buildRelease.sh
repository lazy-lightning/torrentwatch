#!/bin/sh

DOC_FILES="changelog CREDITS LICENSE TODO rss_dl.config.orig"
RELEASE_FILES="$DOC_FILES css/ images/ javascript/ php/"
RELEASE=torrentwatch-dev-$1

rm -rf release
mkdir -p release

cp installer/* release
tar -cf release/tw.scripts.tar --exclude-vcs $RELEASE_FILES

zip -r $RELEASE.zip release/*

