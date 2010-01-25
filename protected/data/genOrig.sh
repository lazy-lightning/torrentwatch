#!/bin/sh
rm source.db.orig
sqlite3 source.db.orig < schema
sqlite3 source.db.orig < schema.populate
# make copy for the test runs
cp source.db.orig source-test.db
sqlite3 source-test.db < schema-test.populate
