#!/bin/sh
cd $(echo $0 | sed 's,[^/]*$,,')
protected/yiic updateFeeds
protected/yiic dbMaintinance
protected/yiic updateTVDB
protected/yiic updateIMDB
protected/yiic pruneCache
