#!/bin/sh

DB='source.db.orig'
TESTDB='source-test.db'
TMP='/tmp'
if [ -d /media/ramdisk ];then
  TMP='/media/ramdisk'
fi

rm -f ./$DB ./$TESTDB $TMP/$DB $TMP/$TESTDB
# build in tmp dir because its faster when the disk is a ramdisk
sqlite3 $TMP/$DB < schema
sqlite3 $TMP/$DB < schema.populate
# make copy for the test runs
cp $TMP/$DB $TMP/$TESTDB
sqlite3 $TMP/$TESTDB < schema-test.populate
# copy back resulting DB's
mv $TMP/$DB $TMP/$TESTDB .
