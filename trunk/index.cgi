#!/usr/bin/php-cgi
<?php

ini_set('include_path', '.:./php');
$test_run = 0;
$firstrun = 0;
$verbosity = 0;

require_once('rss_dl_utils.php');

// This function parses commands sent from a PC browser
function parse_options() {
	global $html_out, $config_values;
	$filler = "<br>";

	if(empty($_SERVER['PATH_INFO']) OR $_SERVER['PATH_INFO'] == '/')
		return FALSE;

	$commands = explode('/', $_SERVER['PATH_INFO']);
	file_put_contents('/tmp/twlog', 'TorrentWatch: '.$_SERVER['PATH_INFO']."\n".print_r($_GET, TRUE), FILE_APPEND);
	switch($commands[1]) {
		case 'firstRun':
			if(isset($_GET['link']))
				update_feed();
			update_global_config();
			$config_values['Settings']['FirstRun'] = FALSE;
			write_config_file();
			break;
		case 'updateFavorite':
			update_favorite();
			break;
		case 'updateFeed':
			update_feed();
			break;
		case 'clearCache':
			clear_cache();
			break;
		case 'setGlobals':
			update_global_config();
			$config_values['Settings']['FirstRun'] = FALSE;
			write_config_file();
			break;
		case 'matchTitle':
			if(($tmp = guess_match(html_entity_decode($_GET['title'])))) {
				$_GET['name'] = trim(strtr($tmp['key'], "._", "  "));
				$_GET['filter'] = trim($tmp['key']);
				if($config_values['Settings']['MatchStyle'] == "glob")
					$_GET['filter'] .= '*';
				$_GET['quality'] = $tmp['data'];
				$_GET['feed'] = $_GET['rss'];
				$_GET['button'] = 'Add';
				$_GET['savein'] = 'Default';
				$_GET['seedratio'] = '-1';
			} else {
				$_GET['name'] = $_GET['title'];
				$_GET['filter'] = $_GET['title'];
				$_GET['quality'] = 'All';
				$_GET['feed'] = $_GET['rss'];
				$_GET['button'] = 'Add';
				$_GET['savein'] = 'Default';
				$_GET['seedratio'] = '-1';
			}
			update_favorite();
			break;
		case 'dlTorrent':
			// Loaded via ajax
			if(stripos($config_values['Settings']['Client'], 'nzb') !== FALSE) 
				$r = client_add_nzb(urldecode($_GET['link']),$_GET['title']);
			else
				$r = client_add_torrent(trim(urldecode($_GET['link'])), $config_values['Settings']['Download Dir'], $_GET['title']);
                        if($r) add_cache($_GET['title']);
			display_history();
			close_html();
			exit(0);
			break;
		case 'clearHistory':
			// Loaded via ajax
			if(file_exists($config_values['Settings']['History']))
				unlink($config_values['Settings']['History']);
			display_history();
			close_html();
			exit(0);
			break;
		default:
			$output = "<script type='text/javascript'>alert('Bad Paramaters passed to ".$_SERVER['PHP_SELF'].":  ".$_SERVER['PATH_INFO']."');</script>";
	}

	if(isset($exec))
		exec($exec, $output);
	if (isset($output)) {
		if(is_array($output))
			$output = implode($filler, $output);
		$html_out .= str_replace("\n", "<br>", "<div class='execoutput'>$output</div>");
		echo $html_out;
		$html_out = "";
	}
	return;
}

function display_global_config() {
	global $config_values, $html_out;

	$savetorrent=$nzbget=$trans122=$trans13x=$btpd="";
	$deepfull=$deeptitle=$deepoff=$verifyepisode="";
	$matchregexp=$matchglob=$matchsimple=$sabnzbd="";
	$onlynewer=$folderclient="";

	switch($config_values['Settings']['Client']) {
		case 'btpd':
			$btpd = 'selected="selected"';
			break;
		case 'transmission1.22':
			$trans122 = 'selected="selected"';
			break;
		case 'transmission1.32':
		case 'transmission1.3x':
			$trans13x = 'selected="selected"';
			break;
		case 'nzbget':
			$nzbget = 'selected="selected"';
			break;
		case 'sabnzbd':
			$sabnzbd = 'selected="selected"';
			break;
                case 'folder':
                        $folderclient = 'selected="selected"';
                        break;
                case 'folder':
                        $folderclient = 'selected="selected"';
                        break;
		default:
			// Shouldn't happen
			break;
	}

	if($config_values['Settings']['Save Torrents'] == 1)
		$savetorrent = 'checked=1';

	switch($config_values['Settings']['Deep Directories']) {
		case 'Full': $deepfull = 'selected="selected"';break;
		case 'Title': $deeptitle = 'selected="selected"'; break;
		default:$deepoff = 'selected="selected"';break;
	}

	if($config_values['Settings']['Verify Episode'] == 1)
		$verifyepisode = 'checked=1';
	if($config_values['Settings']['Only Newer'] == 1)
		$onlynewer = 'checked=1';

	switch($config_values['Settings']['MatchStyle']) {
		case 'glob': $matchglob="selected='selected'";break;
		case 'simple': $matchsimple="selected='selected'";break;
		case 'regexp': 
		default: $matchregexp="selected='selected'";break;
	}

  // Include the templates and append the results to html_out
  ob_start();
  require('templates/global_config.tpl');
  require('templates/feeds.tpl');
  $html_out .= ob_get_contents();
  ob_end_clean();
}


function display_favorites_info($item, $key) {
	global $config_values, $html_out;
	$feed_options = '<option value="all">All</option>';
	if(isset($config_values['Feeds'])) {
		foreach($config_values['Feeds'] as $feed) {
			$feed_options .= '<option value="'.urlencode($feed['Link']).'"';
			if($feed['Link'] == $item['Feed'])
				$feed_options .= ' selected="selected"';
			$feed_options .= '>'.$feed['Name'].'</option>';
		}
	}

  // Dont handle with object buffer, is called inside display_favorites ob_start
  require('templates/favorites_info.tpl');
}

function display_favorites() {
	global $config_values, $html_out;

  ob_start();
  require('templates/favorites.tpl');
  $html_out .= ob_get_contents();
  ob_end_clean();
}

function display_history() {
	global $html_out, $config_values;

	if(file_exists($config_values['Settings']['History'])) {
		$history = array_reverse(unserialize(file_get_contents($config_values['Settings']['History'])));
  } else {
    $history = array();
  }

  ob_start();
  require('templates/history.tpl');
  $html_out .= ob_get_contents();
  ob_end_clean();
}

function close_html() {
	global $html_out, $debug_output, $main_timer;
	echo $html_out;
	$html_out = "";
}
//
//
// MAIN Function
//
//
$main_timer = timer_init();
platform_initialize();

setup_default_config();
if(file_exists(platform_getConfigFile()))
	read_config_file();

$config_values['Global']['HTMLOutput']= 1;
$html_out = "";
$debug_output = "Torrentwatch Debug:";
$verbosity = 0;

parse_options();

display_global_config();
display_favorites();
echo $html_out;
$html_out = "";
ob_flush();flush();

// Feeds
if(isset($config_values['Feeds'])) {
	load_feeds($config_values['Feeds']);
	feeds_perform_matching($config_values['Feeds']);
}

// Comes later incase we just added a torrent	
display_history();

close_html();
unlink_temp_files();

exit(0);
php?>

