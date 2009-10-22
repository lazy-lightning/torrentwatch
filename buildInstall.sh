#!/bin/sh
# Build javascript.min

LFTP_A100_BOOKMARK="PCH"
A100_ADDR="popcorn:8883/HARD_DISK"
LFTP_C200_BOOKMARK="c200"
C200_ADDR="c200:8883/SATA_DISK"
LFTP_NET_BOOKMARK="asodown"

APPINIT="Apps/AppInit/appinit.cgi"
NAME=$(grep name appinfo.json | sed 's/[ ]*.*="\(.*\)",/\1/g')
VERSION=$(grep version appinfo.json | sed 's/[ ]*.*="\(.*\)",/\1/g')
EXCLUDES="install testing protected/data/source.db.BACKUP protected/data/source.db protected/runtime cache buildInstall.sh findNotSvn.sh assets"

if [ -f testing/yuicompressor-2.4.2.jar ]; then
  echo "Building all.min.js"
#  cat $(cat index.html | grep \.js | grep -ve 'all|min' | sed 's,^.*javascript/\(.*\).js.*$,javascript/\1.js,') | java -jar testing/yuicompressor-2.4.2.jar --type js -o javascript/all.min.js
  echo "Done."
fi

# Used to build installation zip
EXSTRING='--exclude-vcs'
for EX in $EXCLUDES; do
  EXSTRING="$EXSTRING --exclude=$EX"
done

tar -cvf install/$NAME.tar . $EXSTRING && cd install && zip $NAME-$VERSION.zip * -x \*.zip

if [ $? != 0 ]; then
  echo "Build Failed"
  exit 1
fi

cat <<EOF

Built install/$NAME-$VERSION.zip

Upload to A100 (A) / C200 (C) / Upload to net (U)
EOF
read CHAR

if [ x"$CHAR" = x"a" -o x"$CHAR" = x"A" ];then
  BM=$LFTP_A100_BOOKMARK
  REMOTE_ADDR=$A100_ADDR
fi

if [ x"$CHAR" = x"c" -o x"$CHAR" = x"C" ]; then
  BM=$LFTP_C200_BOOKMARK
  REMOTE_ADDR=$C200_ADDR
fi

if [ -n $BM ]; then
  echo "put \"$NAME.tar\"" | lftp $BM && wget -q --header "Host: localhost.drives" -O - "http://$REMOTE_ADDR/$APPINIT?install&%2Fshare%2F$NAME.tar"
fi

if [ x"$CHAR" = x"u" -o x"$CHAR" = x"U" ];then
  echo "put \"$NAME-$VERSION.zip\"" | lftp $LFTP_NET_BOOKMARK
fi

