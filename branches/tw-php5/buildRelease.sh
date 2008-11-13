#!/bin/sh

EXE_FILES="index.cgi rss_dl.php"
DOC_FILES="changelog CREDITS LICENSE TODO torrentwatch.config.orig"
RELEASE_FILES="$EXE_FILES $DOC_FILES css/ images/ javascript/ php/ patches/"
RELEASE=torrentwatch-dev-$1

rm -rf release
mkdir -p release

cp installer/* release
tar -cf release/tw.scripts.tar --exclude-vcs $RELEASE_FILES

if [ x"$1" != x"" ]; then
	zip -r $RELEASE.zip release/*
fi

