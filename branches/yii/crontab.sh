#!/bin/sh
cd $(echo $0 | sed 's,[^/]*$,,')

for command in updateFeeds dbMaintinance updateTVDB updateIMDB pruneCache checkVersion
do
  protected/yiic $command
done
