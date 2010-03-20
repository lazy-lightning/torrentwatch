#!/bin/sh
# Build javascript.min

LFTP_A100_BOOKMARK="PCH"
A100_ADDR="popcorn:8883/HARD_DISK"
LFTP_C200_BOOKMARK="c200"
C200_ADDR="c200:8883/SATA_DISK"
LFTP_NET_BOOKMARK="asodown"

APPINIT="Apps/AppInit/appinit.cgi"
NAME=$(grep name appinfo.json | sed 's/[ ]*.*="\(.*\)",/\1/g')
EXCLUDES="install testing protected/data/source-test.db.BACKUP protected/data/source.db.BACKUP protected/data/source.db protected/runtime cache buildInstall.sh findNotSvn.sh assets php.sh images/TvBanners/"

CONFIG_FILES="main.php console.php"

# Dont change below here unless you know what you are doing

IS_SVN_RELEASE=1
CURRENTSVN=$(svn status | egrep -v '^\?|buildInstall.sh|source-test.db')

if [ x"$1" = x"--release" -o x"$1" = x"-r" ]; then
  # dont generate an svn version
  IS_SVN_RELEASE=0
  # release mode, allow changes to appinfo
  CURRENTSVN=$(echo $CURRENTSVN | grep -v appinfo.json)
elif [ x"$1" != x"" ]; then
  cat <<EOD

  Usage:
    $0 [-r|--release]

EOD
  exit;
fi

# test php for basic syntax errors
find ./ -iname \*.php | egrep -v 'svn|yii_framework|PHPUnit' | xargs -L 1 php -l 
if [ ! 0 -eq $? ];then
  cat <<EOD

Please fix lint errors before building package.
EOD
  exit 1;
fi

if [ x"$CURRENTSVN" != x"" -a x"$SKIPSVN" != x"1" ];then
  cat <<EOD

Please commit local changes to svn first.
$CURRENTSVN
EOD
  exit 1
fi

# Generate a new version number (ex: svn20100310) for svn releases
# based on todays date, with multiple releases on same day marked
# in numeric order (ex: svn20100310.1)
if [ $IS_SVN_RELEASE -eq 1 ]; then
  # start with date
  VERSION="svn$(date +%Y%m%d)"
  I=0
  # if that version already exists, increment I untill it doesnt exist
  while [ -f install/$NAME-$VERSION.zip ]; do
    I=$(expr $I + 1)
    VERSION="svn$(date +%Y%m%d).${I}"
  done
  # changeup the appinfo.json file
  cp appinfo.json appinfo.json.orig.$$
  cat appinfo.json | sed "s/\\\$id\\\$/${VERSION}/" > appinfo.json.new
  mv appinfo.json.new appinfo.json
else
# otherwise source the current version from the appinfo.json file
  VERSION=$(cat appinfo.json | grep version | sed 's/.*version="\(.*\)",/\1/')
fi

# replace version number placeholder in config files with actual version
for i in $CONFIG_FILES; do
  cp protected/config/$i protected/config/$i.orig.$$
  cat protected/config/$i | sed "s/\\\$id\\\$/${VERSION}/" > protected/config/$i.new
  mv protected/config/$i.new protected/config/$i
done

FILENAME="$NAME-$VERSION.zip"
echo "Building $FILENAME"
# check if the file already exists, verify the user wants to overwrite
if [ -f install/$FILENAME ]; then
  echo -n "Installl file for this version already exists, overwrite (y/N): "
  read CHAR;
  if [ x"$CHAR" != x"y" -a x"$CHAR" != x"Y" ]; then
    exit;
  fi
fi

echo minify and join some files
# remove all script tags with a src= attribute
cat index.html | sed 's/<script .* src=.*//' > index.html.temp
# build min.js of all scripts with a src= attribute
testing/buildMin.sh 2> /dev/null
# inline content from <link rel="stylesheet"> tags
testing/inline-css-to-html.php index.html.temp index-joined.html >/dev/null
rm index.html.temp
# append javascript/min.js to our new index
echo "<script type='text/javascript'>" >> index-joined.html
cat javascript/min.js >> index-joined.html
echo "</script>" >> index-joined.html

if [ $IS_SVN_RELEASE -eq 0 ];then
  # this is a release, use the joined version as main index.html
  mv index.html index.dev.html
  mv index-joined.html index.html
fi

# if the testdb was orrigionally a sym-link, remove for package building,
# but preserve the link aftewards
TESTDB='protected/data/source-test.db'
LINK=''
if [ -h $TESTDB ]; then
  LINK=$(readlink $TESTDB)
fi

echo generate the initial database
rm -f $TESTDB
cd protected/data && ./genOrig.sh && cd - >/dev/null

# Used to build installation zip
EXSTRING='--exclude-vcs'
for EX in $EXCLUDES; do
  EXSTRING="$EXSTRING --exclude=$EX"
done

echo building inner tar archive
rm install/*.tar
tar -cf install/$NAME.tar . $EXSTRING && cd install && \
echo "building outer zip archive" && zip $FILENAME * -x \*.zip

ZIP_SUCCESS=$?

# regenerate the test db sym-link if needed
if [ x"$LINK" != x"" ];then
  cp $TESTDB $LINK
  rm -f $TESTDB
  ln -s $LINK $TESTDB
fi

# if this is svn and we changed appinfo.json change it back
if [ 1 -eq $IS_SVN_RELEASE ]; then
  mv ../appinfo.json.orig.$$ ../appinfo.json
else
# otherwise swap back the index files from release state to development
  mv ../index.html ../index-joined.html
  mv ../index.dev.html ../index.html
fi

# revert changes made to protected/config/main.php
for i in $CONFIG_FILES; do
  mv ../protected/config/$i.orig.$$ ../protected/config/$i
done

if [ ! $ZIP_SUCCESS -eq 0 ]; then
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
  echo Uploading
  echo "put \"$FILENAME\"" | lftp $LFTP_NET_BOOKMARK

  echo Updating latest.php
  mkdir /tmp/buildinstall.$$
  cd /tmp/buildinstall.$$
  echo "get ../latest.php" | lftp $LFTP_NET_BOOKMARK
  MARKER='// SVN'
  if [ $IS_SVN_RELEASE -eq 0 ]; then
    MARKER='// RELEASE'
  fi
  cat latest.php  | sed "s|.*$MARKER|  echo '$VERSION'; $MARKER|" > latest.php.new
  mv latest.php.new latest.php
  echo "cd ..;put latest.php" | lftp $LFTP_NET_BOOKMARK

  echo "Now available As: "
  echo "http://nmtdvr.com/downloads/$NAME-$VERSION.zip"
fi

