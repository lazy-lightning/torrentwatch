#!/bin/sh
DB='source.db.orig'
TESTDB='source-test.db'

rm -f ./$DB
sqlite3 ./$DB < schema
sqlite3 ./$DB < schema.populate
# make copy for the test runs
cp ./$DB $TESTDB
sqlite3 $TESTDB < schema-test.populate
