#!/bin/sh
# Build javascript.min

LFTP_PCH_BOOKMARK="PCH"
LFTP_NET_BOOKMARK="asodown"

NAME=$(grep name appinfo.json | sed 's/[ ]*.*="\(.*\)",/\1/g')
VERSION=$(grep version appinfo.json | sed 's/[ ]*.*="\(.*\)",/\1/g')
EXCLUDES="install testing protected/data/source.db.BACKUP protected/data/source.db protected/runtime cache buildInstall.sh findNotSvn.sh assets"

if [ -f testing/yuicompressor-2.4.2.jar ]; then
  echo "Building all.min.js"
  cat $(cat index.html | grep \.js | grep -ve 'all|min' | sed 's,^.*javascript/\(.*\).js.*$,javascript/\1.js,') | java -jar testing/yuicompressor-2.4.2.jar --type js -o javascript/all.min.js
  echo "Done."
fi

# Used to build installation zip
EXSTRING='--exclude-vcs'
for EX in $EXCLUDES; do
  EXSTRING="$EXSTRING --exclude=$EX"
done

tar -cvf install/NMTDVR.tar . $EXSTRING && cd install && zip $NAME-$VERSION.zip * -x \*.zip

if [ $? != 0 ]; then
  echo "Build Failed"
  exit 1
fi

cat <<EOF

Built install/$NAME-$VERSION.zip

Upload to NMTand Install (Y/n) or Upload to net (U)
EOF
read CHAR

if [ x"$CHAR" = x"Y" ];then
  echo "put \"$NAME.tar\"" | lftp $LFTP_PCH_BOOKMARK && wget -q -O - "http://localhost.drives:8883/HARD_DISK/Apps/AppInit/appinit.cgi?install&%2Fshare%2F$NAME.tar"
fi

if [ x"$CHAR" = x"U" ];then
  echo "put \"$NAME.zip\"" | lftp $LFTP_NET_BOOKMARK
fi

