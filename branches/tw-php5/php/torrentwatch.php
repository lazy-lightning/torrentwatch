#!/mnt/syb8634/server/php5-cgi -qd register_argc_argv=1
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
