#!/bin/sh
# Used to build installation zip
tar -cvf install/NMTDVR.tar . --exclude-vcs --exclude=install --exclude=testing --exclude=protected/data/source.db.BACKUP --exclude=protected/data/source.db --exclude=protected/runtime --exclude=cache
cd install
zip NMTDVR.zip * -x \*.zip


