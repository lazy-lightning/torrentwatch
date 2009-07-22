#!/bin/sh
# Build javascript.min

#VERSION
VERSION=$(cat appinfo.json | grep version | sed 's/[ ]*.*="\(.*\)",/\1/g')

cat $(cat index.html | grep \.js | grep -ve 'all|min' | sed 's,^.*javascript/\(.*\).js.*$,javascript/\1.js,') | java -jar testing/yuicompressor-2.4.2.jar --type js -o javascript/all.min.js

# Used to build installation zip
tar -cvf install/NMTDVR.tar . --exclude-vcs --exclude=install --exclude=testing --exclude=protected/data/source.db.BACKUP --exclude=protected/data/source.db --exclude=protected/runtime --exclude=cache --exclude=buildInstall.sh --exclude=findNotSvn.sh
cd install
zip NMTDVR-$VERSION.zip * -x \*.zip

echo ''
echo "Built install/NMTDVR-$VERSION.zip"

echo "Upload (Y/n)?"
read CHAR

if [ x"$CHAR" = x"Y" ];then
  echo "cd public_html/downloads;put NMTDVR-$VERSION.zip" | lftp aso
fi
