#!/bin/sh

cd $(dirname $0)

if [ ! -f protected/data/source.db ]; then
  cp protected/data/source.db.orig protected/data/source.db

chmod -R 777 .
