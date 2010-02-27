#!/bin/sh
# Build javascript.min

LFTP_A100_BOOKMARK="PCH"
A100_ADDR="popcorn:8883/HARD_DISK"
LFTP_C200_BOOKMARK="c200"
C200_ADDR="c200:8883/SATA_DISK"
LFTP_NET_BOOKMARK="asodown"

APPINIT="Apps/AppInit/appinit.cgi"
NAME=$(grep name appinfo.json | sed 's/[ ]*.*="\(.*\)",/\1/g')
EXCLUDES="install testing protected/data/source-test.db.BACKUP protected/data/source.db.BACKUP protected/data/source.db protected/runtime cache buildInstall.sh findNotSvn.sh assets php.sh"

# Dont change below here unless you know what you are doing

GENERATE_VERSION=1
REVERT_APPINFO=0
CURRENTSVN=$(svn status | egrep -v '^\?|buildInstall.sh')

# test php for basic syntax errors
find ./ -iname \*.php | egrep -v 'svn|yii_framework|PHPUnit' | xargs -L 1 php -l 
if [ ! 0 -eq $? ];then
  cat <<EOD

Please fix lint errors before building package.
EOD
  exit 1;
fi

if [ x"$1" = x"release" ]; then
  # dont generate an svn version
  GENERATE_VERSION=0
  # release mode, allow changes to appinfo
  CURRENTSVN=$(echo $CURRENTSVN | grep -v appinfo.json)
fi

if [ x"$CURRENTSVN" != x"" ];then
  cat <<EOD

Please commit local changes to svn first.
$CURRENTSVN
EOD
  exit 1
fi

if [ $GENERATE_VERSION -eq 1 ]; then
  # start with date
  VERSION="svn$(date +%Y%m%d)"
  I=0
  # if that version already exists, increment i untill it doesnt exist
  while [ -f install/$NAME-$VERSION.zip ]; do
    I=$(expr $I + 1)
    VERSION="svn$(date +%Y%m%d).${I}"
  done
  # changeup the appinfo.json file
  cat appinfo.json | sed "s/\\\$id\\\$/${VERSION}/" > appinfo.json.new
  mv appinfo.json.new appinfo.json
  REVERT_APPINFO=1
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

echo minify and join some files
testing/buildMin.sh 2> /dev/null
php testing/inline-css-to-html.php >/dev/null

# if the testdb was orrigionally a sym-link, preserve
# that sym-link
TESTDB='protected/data/source-test.db'
if [ -h $TESTDB ]; then
  LINK=$(readlink $TESTDB)
fi

echo generate the initial database
rm -f $TESTDB
cd protected/data && ./genOrig.sh && cd - >/dev/null

# regenerate the sym-link
if [ x"$LINK" != x"" ];then
  cp $TESTDB $LINK
  rm -f $TESTDB
  ln -s $LINK $TESTDB
fi

# Used to build installation zip
EXSTRING='--exclude-vcs'
for EX in $EXCLUDES; do
  EXSTRING="$EXSTRING --exclude=$EX"
done

echo building inner tar archive
rm install/*.tar
tar -cf install/$NAME.tar . $EXSTRING && cd install && \
echo "building outer zip archive" && zip $FILENAME * -x \*.zip

# revert changes made to appinfo.json
if [ 1 -eq $REVERT_APPINFO ]; then
  FILE="../appinfo.json"
  svn revert $FILE >/dev/null
fi

if [ $? != 0 ]; then
  echo "Build Failed"
  exit 1
fi

cat <<EOF

Built install/$FILENAME

Upload to A100 (A) / C200 (C) / Upload to net (U) / Delete (D)
EOF
read CHAR

if [ x"$CHAR" = x"D" ];then
  echo "Deleting $FILENAME"
  echo
  rm $FILENAME
  exit
fi

if [ x"$CHAR" = x"a" -o x"$CHAR" = x"A" ];then
  BM=$LFTP_A100_BOOKMARK
  REMOTE_ADDR=$A100_ADDR
fi

if [ x"$CHAR" = x"c" -o x"$CHAR" = x"C" ]; then
  BM=$LFTP_C200_BOOKMARK
  REMOTE_ADDR=$C200_ADDR
fi

if [ x"$BM" != x"" ]; then
  echo "put \"$FILENAME.tar\"" | lftp $BM && wget -q --header "Host: localhost.drives" -O - "http://$REMOTE_ADDR/$APPINIT?install&%2Fshare%2F$NAME.tar"
fi

if [ x"$CHAR" = x"u" -o x"$CHAR" = x"U" ];then
  echo "put \"$FILENAME\"" | lftp $LFTP_NET_BOOKMARK
  echo "http://nmtdvr.com/downloads/$NAME-$VERSION.zip"
fi

