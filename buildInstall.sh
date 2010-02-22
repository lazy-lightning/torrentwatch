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
EXCLUDES="install testing protected/data/source.db.BACKUP protected/data/source.db protected/runtime cache buildInstall.sh findNotSvn.sh assets php.sh"

if [ $VERSION != ${VERSION#svn} ]; then
  OLDVERSION=$VERSION
  VERSION="svn$(date +%Y%m%d)"
  I=0
  while [ -f install/$NAME-$VERSION.zip ]; do
    I=$(expr $I + 1)
    VERSION="svn$(date +%Y%m%d).${I}"
  done
  cat appinfo.json | sed "s/${OLDVERSION}/${VERSION}/" > appinfo.json.new
  mv appinfo.json.new appinfo.json
fi

FILENAME="$NAME-$VERSION.zip"
echo "Building $FILENAME"
if [ -f install/$FILENAME ]; then
  echo -n "Installl file for this version already exists, overwrite (y/N): "
  read line;
  if [ x"$line" != x"y" -o x"$line" != x"Y" ]; then
    exit;
  fi
fi

# test php for basic syntax errors
find ./ -iname \*.php | egrep -v 'svn|yii_framework' | xargs -L 1 php -l
if [ ! 0 -eq $? ];then
  echo <<EOD

Please fix lint errors before building package.
EOD
  exit;
fi

# minify/join some files
testing/buildMin.sh 2> /dev/null
php testing/inline-css-to-html.php >/dev/null

# Used to build installation zip
EXSTRING='--exclude-vcs'
for EX in $EXCLUDES; do
  EXSTRING="$EXSTRING --exclude=$EX"
done

rm install/*.tar
tar -cf install/$NAME.tar . $EXSTRING && cd install && zip $FILENAME * -x \*.zip

if [ $? != 0 ]; then
  echo "Build Failed"
  exit 1
fi

cat <<EOF

Built install/$FILENAME

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
  echo "put \"$FILENAME\"" | lftp $LFTP_NET_BOOKMARK
  echo "http://nmtdvr.com/downloads/$NAME-$VERSION.zip"
fi

