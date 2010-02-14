<?php
/**
 * This was mostly autogenerated by the following search/replace against protected/data/schema.populate:
 *   s/INSERT INTO dvrConfig .* VALUES(\(.*\),\(.*\),\(.*\));/array('key'=>\1,'value'=>\2,'dvrConfigCategory_id'=>\3),/
 *   s/^--/\/\//
 */

return array(
// Global Options
array('key'=>'client','value'=>'clientFolder','dvrConfigCategory_id'=>NULL),
array('key'=>'downloadDir','value'=>'/share/Download/','dvrConfigCategory_id'=>NULL),
array('key'=>'watchDir','value'=>'/share/','dvrConfigCategory_id'=>NULL),
array('key'=>'saveFile','value'=>1,'dvrConfigCategory_id'=>NULL),
array('key'=>'tempDir','value'=>'/tmp','dvrConfigCategory_id'=>NULL),
array('key'=>'torClient','value'=>'clientFolder','dvrConfigCategory_id'=>NULL),
array('key'=>'nzbClient','value'=>'clientFolder','dvrConfigCategory_id'=>NULL),
array('key'=>'webItemsPerLoad','value'=> 50,'dvrConfigCategory_id'=> NULL),
array('key'=>'maxItemsPerFeed','value'=> 100,'dvrConfigCategory_id'=> NULL),
array('key'=>'timezone','value'=> 'America/Los_Angeles','dvrConfigCategory_id'=> NULL),
array('key'=>'webuiTheme','value'=> 'classic','dvrConfigCategory_id'=> NULL),
array('key'=>'gayauiTheme','value'=> 'gaya','dvrConfigCategory_id'=> NULL),
array('key'=>'matchingTimeLimit','value'=>24,'dvrConfigCategory_id'=> NULL),
// BTPD
array('key'=>'executable','value'=>'/mnt/syb8634/bin/btcli','dvrConfigCategory_id'=>2),
array('key'=>'directory','value'=>'/share/.btpd','dvrConfigCategory_id'=>2),
// Transmission 1.22
array('key'=>'executable','value'=>'/mnt/syb8634/bin/transmission-remote','dvrConfigCategory_id'=>4),
array('key'=>'directory','value'=>'/share/.transmission','dvrConfigCategory_id'=>4),
// nzbget
array('key'=>'executable','value'=>'/mnt/syb8634/bin/nzbget','dvrConfigCategory_id'=>6),
array('key'=>'nzbgetConf','value'=>'/share/.nzbget/nzbget.conf','dvrConfigCategory_id'=>6),
// Transmission RPC
array('key'=>'baseApi','value'=>'http://localhost:9091/transmission/','dvrConfigCategory_id'=>5),
array('key'=>'seedRatio','value'=>0,'dvrConfigCategory_id'=>5),
array('key'=>'username','value'=>'','dvrConfigCategory_id'=>5),
array('key'=>'password','value'=>'','dvrConfigCategory_id'=>5),
// SABnzbd+
array('key'=>'category','value'=>'Default','dvrConfigCategory_id'=>7),
array('key'=>'baseApi','value'=>'http://127.0.0.1:8080/sabnzbd/','dvrConfigCategory_id'=>7),
// CTorrent
array('key'=>'baseApi','value'=> 'http://127.0.0.1:9002/','dvrConfigCategory_id'=> 3),
array('key'=>'username','value'=> 'nmt','dvrConfigCategory_id'=> 3),
array('key'=>'password','value'=> '1234','dvrConfigCategory_id'=> 3),
array('key'=>'startPaused','value'=> '0','dvrConfigCategory_id'=> 3),
);

