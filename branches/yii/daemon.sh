#!/bin/sh

cd $(echo $0 | sed 's,[^/]*$,,')

if [ ! -f protected/data/source.db ]; then
  cp protected/data/source.db.orig protected/data/source.db
fi

chmod -R 777 .
