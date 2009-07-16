#!/bin/sh
rm source.db.orig
sqlite3 source.db.orig < schema
sqlite3 source.db.orig < schema.populate
