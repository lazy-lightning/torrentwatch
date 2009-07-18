#!/bin/sh

cd $(echo $0 | sed 's,[^/]*$,,')

if [ ! -f protected/data/source.db ]; then
  cp protected/data/source.db.orig protected/data/source.db
fi

if [ ! -d protected/runtime ]; then
  mkdir protected/runtime
fi

if [ ! -d cache ]; then
  mkdir cache
fi

chmod -R 777 .
