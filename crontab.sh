#!/bin/sh
cd $(echo $0 | sed 's,[^/]*$,,')

for command in updateFeeds dbMaintinance 'scrape all' pruneCache checkVersion
do
  protected/yiic $command
done
