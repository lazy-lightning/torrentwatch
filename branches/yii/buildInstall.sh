#!/bin/sh
# Build javascript.min

#VERSION
VERSION=$(cat appinfo.json | grep version | sed 's/[ ]*.*="\(.*\)",/\1/g')

cat $(cat index.html | grep \.js | grep -ve 'all|min' | sed 's,^.*javascript/\(.*\).js.*$,javascript/\1.js,') | java -jar testing/yuicompressor-2.4.2.jar --type js -o javascript/all.min.js

# Used to build installation zip
tar -cvf install/NMTDVR.tar . --exclude-vcs --exclude=install --exclude=testing --exclude=protected/data/source.db.BACKUP --exclude=protected/data/source.db --exclude=protected/runtime --exclude=cache
cd install
zip NMTDVR-$VERSION.zip * -x \*.zip

echo ''
echo "Built install/NMTDVR-$VERSION.zip"


