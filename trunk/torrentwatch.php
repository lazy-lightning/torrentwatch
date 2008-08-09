#!/mnt/syb8634/server/php
<?php

require_once('rss_dl_utils.php');

function usage() {
	global $argv;
	echo "$argv[0] - Torrent Watch Folder\n";
	echo "Valid Options\n";
	echo "  check     - check for new torrents\n\n";
}

function parse_options() {
	global $argc, $argv;
	if($argc != 2) {
		_debug("Wrong number of options passed to torrentwatch.php\n",0);
		usage();
		exit(1);
	}
	switch($argv[1]) {
		case 'check';
			break;
		default:
			usage();
			exit(1);
	}
}

function add_torrent($directory, $filename, $dest) {
	global $config_values, $hit;
	$btcli = '/mnt/syb8634/bin/btcli';
	$btcli_add = 'add -d';
	$btcli_connect='-d /opt/sybhttpd/localhost.drives/HARD_DISK/.btpd/';
	$btcli_exec="$btcli $btcli_connect";

	$trans_remote = '/mnt/syb8634/bin/transmission-remote';
	$trans_connect = '-g /share/.transmission';
	$trans_exec = "$trans_remote $trans_connect";
	$trans_down_dir = '-f';
	$trans_add = '-a';

	$hit = 1;
	if($config_values['Settings']['Deep Directories']) {
		preg_match("/(.*)\.torrent/", $filename, $matches);
		$orig = $dest;
		$dest = "$dest/$matches[1]";
		_debug("Deep Directorys, change dest to $dest\n", 1);
	}
	exec("mkdir -p \"$dest\"");
	if($config_values['Settings']['Client'] == "btpd")
		exec("$btcli_exec $btcli_add \"$dest\" \"$directory/$filename\"", $output, $return);
	else if($config_values['Settings']['Client'] == "transmission") {
		/* Transmission wont let us explicitly set download dir till they release 1.30 */
		$trans_dl = transmission_get_dl_dir();
		if(!($trans_dl == $dest))
			exec("$trans_exec $trans_down_dir \"$dest\"");
		exec("$trans_exec $trans_add \"$directory/$filename\"", $output, $return);
		/* sometimes our download ends up here anyways, depending on when the file gets written to disk */
		if(!($trans_dl == $dest))
			exec("$trans_exec $trans_down_dir \"$trans_dl\"");
	} else {
		_debug("Invalid Torrent Client: ".$config_values['Settings']['Client']."\n",0);
		exit(1);
	}
	if($return == 0)
		_debug("Starting: $filename in $dest\n",0);
	else 
		_debug("Failed Starting: $filename  Return code $return\n",0);
	if($config_values['Settings']['Save Torrents'])
		rename("$directory/$filename", "$dest/".basename($filename));
	else
		unlink("$directory/$filename");
}

function check_for_torrents($directory, $dest) {
	if($handle = opendir($directory)) {
		while(false !== ($file = readdir($handle))) {
			if(preg_match('/\.torrent$/', $file))
				add_torrent($directory, $file, $dest);
		}
		closedir($handle);
	} else {
		_debug("torrentwatch.php: Couldn't read Directory: $directory\n", 0);
		exit(1);
	}
}

//
//
// MAIN Function
//
//

read_config_file();
parse_options();
if(!isset($config_values['Settings']['Watch Dir']) or
		!isset($config_values['Settings']['Download Dir'])) {
	_debug("torrentwatch.php: Bad Config\n\n", 0);
	exit(1);
}
$hit = 0;
check_for_torrents($config_values['Settings']['Watch Dir'], $config_values['Settings']['Download Dir']);
if(!$hit)
	_debug("No New Torrents to add\n", 0);
exit(0);

?>
