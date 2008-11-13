<?php

if(file_exists("/etc/init_nmt"))
	$platform = "NMT";
else
	$platform = "Linux";

function platform_install() {
	global $platform;
	switch($platform) {
		case 'NMT':
			symlink_force(platform_getInstallRoot(), platform_getWebRoot()."/torrentwatch");
			symlink_force("/mnt/syb8634/server/php5-cgi", "/usr/bin/php-cgi");
			break;
		case 'Linux':
		default:
			break;
	}
}
			

function platform_getConfigFile() {
	return platform_getUserRoot()."/torrentwatch.config";
}

function platform_getUserRoot() {
	global $platform;
	switch($platform) {
		case 'NMT':
			return "/share/.torrents";
			break;
		case 'Linux':
		default:
			return "~/.torrents";	
			break;
	}
}

// this function is only called from platform_install()
function platform_getWebRoot() {
	global $platform;
	switch($platform) {
		case 'NMT':
			return "/opt/sybhttpd/default/";
			break;
		case 'Linux':
		default:
			return "/var/www";
			break;
	}
}	

function platform_getDownloadDir() {
	global $platform;
	switch($platform) {
		case 'NMT':
			return "/share/Download";
			break;
		case 'Linux':
		default:
			return "~/Download";
			break;
	}
}

function platform_getInstallRoot() {
	global $platform, $argv;
	return dirname(realpath($argv[0]));
}

?>
