#!/bin/sh

EXE_FILES="index.cgi rss_dl.php inspector.cgi"
DOC_FILES="changelog CREDITS LICENSE TODO"
RELEASE_FILES="$EXE_FILES $DOC_FILES .htaccess css/ images/ javascript/ php/ html/"
RELEASE=torrentwatch-dev-$1

rm -rf release
mkdir -p release

cp -rf patches/* release
cp installer/* release
tar -cf release/tw.scripts.tar --exclude-vcs $RELEASE_FILES

if [ x"$1" != x"" ]; then
        ln -s release $RELEASE
	zip -r $RELEASE.zip $RELEASE/*
        rm $RELEASE
fi

