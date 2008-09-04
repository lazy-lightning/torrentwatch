#!/mnt/syb8634/server/php5-cgi
<?php

ini_set('include_path', '.:/share/.torrents/php');
$config_file = '/share/.torrents/rss_dl.config';
$test_run = 0;

require_once('rss_dl_utils.php');

// Custom parsing for nmt browser
// Doesn't do anything special yet
function parse_options_localhost() {
	global $html_out, $config_values;
	$filler = "<br>";
	$exit = TRUE;

	if(isset($_GET['mode'])) {
		switch($_GET['mode']) {
			case 'updatefavorite':
				update_favorite();
				display_favorites();
				break;
			case 'updatefeed':
				update_feed();
				display_global_config();
				break;
			case 'showfeed':
				echo $html_out;
				$html_out = "";
				if($_GET['feed'] == 'all') {
					load_feeds($config_values['Feeds']);
					feeds_perform_matching($config_values['Feeds']);
				} else {
					$feed[] = $config_values['Feeds'][$_GET['feed']];
					load_feeds($feed);
					feeds_perform_matching($feed);
				}
				break;	
			case 'emptycache':
				$exec = "rm -f ".$config_values['Settings']['Cache Dir']."/*";
				$exit = FALSE;
				break;
			case 'setglobals':
				$config_values['Settings']['Download Dir']=urldecode($_GET['downdir']);
				$config_values['Settings']['Watch Dir']=urldecode($_GET['watchdir']);
				$config_values['Settings']['Deep Directories']=urldecode($_GET['deepdir']);
				$config_values['Settings']['Verify Episode']=(isset($_GET['verifyepisodes']) ? 1 : 0);
					$config_values['Settings']['Save Torrents']=(isset($_GET['savetorrents']) ? 1 : 0);
				$config_values['Settings']['Client']=urldecode($_GET['client']);
				write_config_file();
				display_global_config();
				
				break;
			case 'matchtitle':
				if(($tmp = guess_match(html_entity_decode($_GET['title'])))) {
					$_GET['name'] = $tmp['key'];
					$_GET['filter'] = $tmp['key'];
					$_GET['quality'] = $tmp['data'];
					$_GET['feed'] = $_GET['rss'];
					$_GET['button'] = 'Add';
					$_GET['savein'] = 'Default';
					update_favorite();
				} else
					$output = "Could not generate Match\n";
				display_favorites();
				break;
			case 'dltorrent':
				update_progress_bar(0, 'Starting '.$_GET['title']);
				client_add_torrent(trim(urldecode($_GET['link'])), $config_values['Settings']['Download Dir']);
				$exit = FALSE;
				break;
			default:
				$output = "Bad Paramaters passed to index.cgi";
		}
	}

	if(isset($exec)) {
		exec($exec, $output);
		$html_out .= "<div class='execoutput'>".implode($filler, $output)."</div>";
		echo($html_out);
		$html_out = "";
	} else if (isset($output)) {
		$html_out .= str_replace("\n", "<br>", "<div class='execoutput'>$output</div>");
		echo $html_out;
		$html_out = "";
	}

	if(isset($exit)) {
		close_html();
		exit(0);
	}

	return;
}

function parse_options() {
	global $html_out, $config_values;
	$filler = "<br>";

	if(isset($_GET['mode'])) {
		switch($_GET['mode']) {
			case 'updatefavorite':
				update_favorite();
				break;
			case 'updatefeed':
				update_feed();
				break;
			case 'clear_cache':
				clear_cache();
				break;
			case 'setglobals':
				$config_values['Settings']['Download Dir']=urldecode($_GET['downdir']);
				$config_values['Settings']['Watch Dir']=urldecode($_GET['watchdir']);
				$config_values['Settings']['Deep Directories']=urldecode($_GET['deepdir']);
				$config_values['Settings']['Verify Episode']=(isset($_GET['verifyepisodes']) ? 1 : 0);
					$config_values['Settings']['Save Torrents']=(isset($_GET['savetorrents']) ? 1 : 0);
				$config_values['Settings']['Client']=urldecode($_GET['client']);
				write_config_file();
				// This is always called in a hidden frame, so display new config and exit
				display_global_config();
				$html_out .= '<script type="text/javascript">updateFrameCopyDiv("configuration");updateFrameFinished();</script>';
				close_html();
				exit(0);
				break;
			case 'matchtitle':
				if(($tmp = guess_match(html_entity_decode($_GET['title'])))) {
					$_GET['name'] = $tmp['key'];
					$_GET['filter'] = $tmp['key'];
					$_GET['quality'] = $tmp['data'];
					$_GET['feed'] = $_GET['rss'];
					$_GET['button'] = 'Add';
					$_GET['savein'] = 'Default';
					update_favorite();
				} else
					$output = "Could not generate Match\n";
				break;
			case 'dltorrent':
				// Dont display full information, this link is loaded in a hidden iframe
				client_add_torrent(trim(urldecode($_GET['link'])), $config_values['Settings']['Download Dir']);
				display_history();
				$html_out .= "<script type='text/javascript'>updateFrameCopyDiv('history');updateFrameFinished();</script>";
				close_html();
				exit(0);
				break;
			case 'clearhistory':
				// Called in hidden div
				if(file_exists($config_values['Settings']['History']))
					unlink($config_values['Settings']['History']);
				display_history();
				$html_out .= '<script type="text/javascript">updateFrameCopyDiv("history");updateFrameFinished();</script>';
				close_html();
				exit(0);
				break;
			default:
				$output = "Bad Paramaters passed to index.cgi";
		}
	}

	if(isset($exec)) {
		exec($exec, $output);
		$html_out .= "<div class='execoutput'>".implode($filler, $output)."</div>";
		echo($html_out);
		$html_out = "";
	} else if (isset($output)) {
		$html_out .= str_replace("\n", "<br>", "<div class='execoutput'>$output</div>");
		echo $html_out;
		$html_out = "";
	}
	return;
}
function display_global_config() {
	global $config_values, $html_out;

	$html_out .= '<div class="dialog_window" id="configuration">'."\n";	
	$html_out .= '<h2 class="dialog heading">Configuration</h2>';
	$html_out .= '<form target="update_frame" action="index.cgi" id="config_form"><input type="hidden" name="mode" value="setglobals">';
	$html_out .= '<div class="config_torrentclient">';
	$html_out .= '<label class="category">Client Settings</label>';
	$html_out .= '<label class="item" title="Which torrent client to use">Torrent Client:</label>';
	$html_out .= '<select name="client">';
	$btpd = "";
	$trans122 = "";
	$trans132 = "";
	switch($config_values['Settings']['Client']) {
		case 'btpd':
			$btpd = 'selected="selected"';
			break;
		case 'transmission1.22':
			$trans122 = 'selected="selected"';
			break;
		case 'transmission1.32':
		case 'transmission1.3x':
			$trans132 = 'selected="selected"';
			break;
		default:
			// Shouldn't happen
			break;
	}
	$html_out .= '<option value="btpd" '.$btpd.'>BTPD</option>';
	$html_out .= '<option value="transmission1.22" '.$trans122.'>Transmission 1.22</option>';
	$html_out .= '<option value="transmission1.3x" '.$trans132.'>Transmission 1.3x</option></select></div>';

	$html_out .= '<div class="config_downloaddir" title="Default directory to start torrents in"><label class="item">Download Directory:</label>';
	$html_out .= '<input type="text" name="downdir" value='.$config_values['Settings']['Download Dir'].'></div>';
	$html_out .= '<div class="config_watchdir">';
	$html_out .= '<label class="category">Torrent Settings</label>';
	$html_out .= '<label class="item" title="Directory to look for new .torrents">Watch Directory:</label>';
	$html_out .= '<input type="text" name="watchdir" value='.$config_values['Settings']['Watch Dir'].'></div>';

	$html_out .= '<div class="config_savetorrent">';
	$html_out .= '<input type="checkbox" name="savetorrents" value=1 ';
	if($config_values['Settings']['Save Torrents'] == 1)
		$html_out .= 'checked=1';
	$html_out .= '><label class="item" title="Save torrent to download directory">Save .torrent</label></div>';

	$html_out .= '<div class="config_deepdir"><label class="item" title="Save downloads in multi-directory structure">Deep Directories:</label>';
	$tmp1 = $tmp2 = $tmp3 = "";
	switch($config_values['Settings']['Deep Directories']) {
		case 'Full':
			$tmp1 = 'selected="selected"';
			break;
		case 'Title':
			$tmp2 = 'selected="selected"';
			break;
		default:
			$tmp3 = 'selected="selected"';
			break;
	}
	$html_out .= '<select name="deepdir">';
	$html_out .= '<option value="Full" '.$tmp1.'>Full Name</option>';
	$html_out .= '<option value="Title" '.$tmp2.'>Show Title</option>';
	$html_out .= '<option value="0" '.$tmp3.'>Off</option></select></div>';

	$html_out .= '<div class="config_verifyepisodes"i title="Try not to download duplicate episodes"><label class="category">Favorites Settings</label>';

	$html_out .= '<input type="checkbox" name="verifyepisodes" value=1 ';
	if($config_values['Settings']['Verify Episode'] == 1)
		$html_out .= 'checked=1';
	$html_out .= '><label class="item">Verify Episodes</label>';
	$html_out .= '</div>';
	$html_out .= _jscript("showLayer('progressDiv');setProgress('progressBar',50);setText('progressBar', 'Saving');submitform('config_form')", 'Save');
	$html_out .= _jscript("toggleMenu('configuration')", 'Close');
	$html_out .= _jscript("toggleMenu('feeds')", 'Feeds');
	$html_out .= '</form></div>'."\n";

	// Feeds
	$html_out .= '<div class="dialog_window" id="feeds">';
	$html_out .= '<label class="Category">Feeds</label>';
	foreach($config_values['Feeds'] as $key => $feed) {
		$html_out .= '<div class="feeditem">'."\n";
		$html_out .= '<form action="index.cgi" class="feedform"><input type="hidden" name="mode" value="updatefeed">'."\n";
		$html_out .= '<input type="hidden" name="idx" value="'.$key.'">';
		$html_out .= '<input class="del" type="submit" name="button" value="Delete">'."\n";
	  $html_out .= '<label class="item">'.$feed['Name'].': '.$feed['Link'].'</label></form></div>'."\n";
	}
	$html_out .= '<div class="feeditem">'."\n";
	$html_out .= '<form action="index.cgi" class="feedform"><input type="hidden" name="mode" value="updatefeed">'."\n";
	$html_out .= '<input type="submit" class="add" name="button" value="Add">';
	$html_out .= '<label class="item">New Feed:</label><input type="text" name="link">'."\n";
	$html_out .= _jscript("toggleMenu('feeds')", "Close");
	$html_out .= _jscript("toggleMenu('configuration')", "Back");
	$html_out .= '</form></div></div>'."\n";

}

function display_favorites_info($item, $key) {
	global $config_values, $html_out;
	$style = "";
	$html_out .= '<div class="FavInfo" id="favorite_'.$key.'" '.$style.'>'."\n";
	$html_out .= '<form action="index.cgi">'."\n";
	$html_out .= '<input type="hidden" name="mode" value="updatefavorite">'."\n";
	$html_out .= '<input type="hidden" name="idx" value="'.$key.'">'."\n";
	$html_out .= '<div class="favorite_name"><label class="item" title="Name of the Favorite">Name:</label>';
	$html_out .= '<input type="text" name="name" value="'.$item['Name'].'"></div>'."\n";
	$html_out .= '<div class="favorite_filter"><label class="item" title="Regexp filter, use .* to match all">Filter:</label>';
	$html_out .= '<input type="text" name="filter" value="'.$item['Filter'].'"></div>'."\n";
	$html_out .= '<div class="favorite_not"><label class="item"i title="Regexp Not Filter">Not:</label>';
	$html_out .= '<input type="text" name="not" value="'.$item['Not'].'"></div>'."\n";
	$html_out .= '<div class="favorite_savein"><label class="item" title="Save Directory or Default">Save In:</label>';
	$html_out .= '<input type="text" name="savein" value="'.$item['Save In'].'"></div>'."\n";
	$html_out .= '<div class="favorite_episodes"><label class="item" title="Regexp Episode filter in form of 2x[1-8]">Episodes:</label>';
	$html_out .= '<input type="text" name="episodes" value="'.$item['Episodes'].'"></div>'."\n";
	$html_out .= '<div class="favorite_feed"><label class="item" title="Feed to match against">Feed:</label><select name="feed">'."\n";
	$html_out .= '<option value="all">All</option>'."\n";
	foreach($config_values['Feeds'] as $feed) {
		$html_out .= '<option value="'.urlencode($feed['Link']).'"';
		if($feed['Link'] == $item['Feed'])
			$html_out .= ' selected="selected"';
		$html_out .= '>'.$feed['Name'].'</option>'."\n";
	}
	$html_out .= '</select></div>'."\n";
	$html_out .= '<div class="favorite_quality"><label class="item" title="Regexp Filter against full title">Quality:</label>';
	$html_out .= '<input type="text" name="quality" value="'.$item['Quality'].'"></div>'."\n";
	$html_out .= '<input type="submit" class="add" name="button" value="Update">'."\n";
	$html_out .= '<input type="submit" class="del" name="button" value="Delete">'."\n";
	$html_out .= _jscript("toggleMenu('favorites')", "Close").'</form></div>'."\n";
	// Display the fav that was just updated
	if(isset($_GET['idx']) && $_GET['idx'] == $key) {
		$html_out .= "<script type='text/javascript'>";
		$html_out .= 'toggleFav("favorite_'.$_GET['idx'].'");</script>'."\n";
	}
}

function display_favorites() {
	global $config_values, $html_out;
	$html_out .= '<div class="dialog_window" id="favorites">';
	$html_out .= '<div class="Favorite"><ul>';
	foreach($config_values['Favorites'] as $key => $item) {
		$html_out .= '<li>'._jscript("toggleFav('favorite_".$key."')", $item['Name']).'</li>'."\n";
	}
	$html_out .= '<li>'._jscript("toggleFav('favorite_new')", "New Favorite").'</li>'."\n";
	$html_out .= '</ul></div>';
	array_walk($config_values['Favorites'], 'display_favorites_info');
	$item = array('Name' => '', 
	              'Filter' => '', 
	              'Not' => '', 
	              'Save In' => 'Default',
	              'Episodes' => '', 
	              'Feed' => '', 
	              'Quality' => '');
	display_favorites_info($item, "new");
	$html_out .= '<div class="clear"></div>'."\n";
	$html_out .= '</div>'."\n";
}

function display_topmenu() {
	global $html_out, $config_values;
	$html_out .= '<div class="mainoptions" id="mainoptions">'."\n";
	$html_out .= '<ul>'."\n";
	$html_out .= '<li id="favoritesMenu">'._jscript("toggleMenu('favorites')", "Favorites").'</li>';
	$html_out .= '<li id="config">'._jscript("toggleMenu('configuration')", "Configure").'</li>';
	$html_out .= '<li class="divider">&nbsp;</li>';
	$html_out .= '<li id="view">'._jscript("toggleMenu('history')", "View History").'</li>';
	$html_out .= '<li id="divider">&nbsp;</li>';
	$html_out .= '<li id="empty">'._jscript("toggleMenu('clear_cache')", 'Empty Cache').'</li>';
	switch($config_values['Settings']['Client']) {
		case 'btpd':
			$html_out .= '<li id="webui"><a href="http://';
			if($_SERVER['REMOTE_ADDR'] == "127.0.0.1")
				$html_out .= '127.0.0.1';
			else
				$html_out .= 'popcorn';
			$html_out .= ':8883/torrent/bt.cgi">BitTorrent WebUI</a></li>';
			break;
		case 'transmission1.3x':
		case 'transmission1.32':
			$html_out .= '<li id="webui"><a href="http://popcorn:9091/transmission/web/">Transmission</a></li>';
			break;
		case 'transmission1.22':
			$html_out .= '<li id="webui"><a href="http://popcorn:8077/">Clutch</a></li>';
			break;
	}
	$html_out .= '</ul>'."\n";
	$html_out .= '</div>'."\n";
}

function display_history() {
	global $html_out, $config_values;

	$html_out .= '<div class="dialog_window" id="history"><ul>'."\n";
	if(file_exists($config_values['Settings']['History'])) {
		$history = unserialize(file_get_contents($config_values['Settings']['History']));

		$html_tmp = '';
		foreach($history as $item) {
			// History is written to file in reverse order
			$html_tmp = '<li>'.$item['Date'].' - '.$item['Title'].'</li>'.$html_tmp;
		}
		$html_out .= $html_tmp;
		$html_out .= '</ul>';
	}
	$html_out .= _jscript("toggleMenu('history')", "Close");
	$html_out .= _jscript("updateFrameLoad('index.cgi?mode=clearhistory', 'Clearing Cache');", "Clear");
	$html_out .= "</div>";
}

function display_clear_cache() {
	global $html_out;
	$html_out .= '<div class="dialog_window" id="clear_cache">'."\n";
	$html_out .= '<h2 class="dialog heading">Which Cache</h2>';
	$html_out .= _jscript("toggleMenu('clear_cache')", 'Close');
	$html_out .= '<a href="index.cgi?mode=clear_cache&type=feeds">Feeds</a>';
	$html_out .= '<a href="index.cgi?mode=clear_cache&type=torrents">Torrents</a>';
	$html_out .= '<a href="index.cgi?mode=clear_cache&type=all">All</a>';
	$html_out .= '</div>'."\n";
}

function display_filter_bar() {
	global $html_out;
	$html_out .= '<div id="filterbar"><ul>';
	$html_out .= '<li id="filter_all">'._jscript("filterFeeds('all')", "All").'</li>';
	$html_out .= '<li id="filter_matching">'._jscript("filterFeeds('matching')", "Matching").'</li>';
	$html_out .= '<li id="filter_downloaded">'._jscript("filterFeeds('downloaded')", "Downloaded").'</li>';
	$html_out .= '</ul></div>'."\n";
}

function display_context_menu() {
	global $html_out;
	$html_out .= '<div class="context_menu" id="CM1"><ul>';
	$html_out .= '<li>'._jscript('contextAddToFav()', 'Add to Favorites').'</li>';
	$html_out .= '<li>'._jscript('contextDLNow()', 'Start Downloading').'</li>';
	$html_out .= '</ul></div>'."\n";
}
	
function display_hidden_iframe() {
	global $html_out;
	$html_out .= '<iframe id="update_frame" src="about:blank" name="update_frame"></iframe>';
}

function set_default_div() {
	global $html_out;
	
	if(!isset($_GET['mode']))
		return;
	$html_out .= '<script type="text/javascript">';
	switch($_GET['mode']) {
		case 'updatefavorite':
		case 'matchtitle':
			$html_out .= 'toggleMenu(\'favorites\');';
			if(isset($_GET['button']) && $_GET['button'] != 'Delete')
				$html_out .= 'toggleFav(\'favorite_'.$_GET['idx'].'\');';
			break;
		case 'updatefeed':
			$html_out .= 'toggleMenu(\'feeds\');';
			break;
		case 'setglobals':
			$html_out .= 'toggleMenu(\'configuration\');';
			break;
	}
	$html_out .= '</script>'."\n";
}

function close_html() {
	global $html_out, $debug_output;
	$html_out .= "<div class='clear'></div>\n<div class='timer'>Page Took ";
	$html_out .= number_format(timer_get_time(), 4)."s to load</div>";
	$html_out .= "<div class='rss_debug'>$debug_output</div>";
	$html_out .= "</body></html>\n";
	echo $html_out;
	$html_out = "";
}
//
//
// MAIN Function
//
//
timer_init();
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<title>Torrentwatch</title>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
<meta http-equiv='expires' content='0'>
<?php
if($_SERVER["REMOTE_ADDR"] == '127.0.0.1') {
	echo ('<link rel="Stylesheet" type="text/css" href="css/torrentwatch.local.css?'.time().'"></link>');
	echo ('<script type="text/javascript" src="javascript/torrentwatch.local.js"></script>');
} else {
	echo ('<link rel="Stylesheet" type="text/css" href="css/torrentwatch.css?'.time().'"></link>');
	echo ('<script type="text/javascript" src="javascript/torrentwatch.js?'.time().'"></script>');
	echo ('<script type="text/javascript" src="javascript/webtoolkit.contextmenu.js?'.time().'"></script>');
	echo ('<script type="text/javascript">');
	echo ('SimpleContextMenu.setup({"preventDefault":false, "preventForms":false});');
	echo ('SimpleContextMenu.attach("torrent", "CM1");</script>');
	echo ('<script type="text/javascript" src="javascript/webappers.com.progress.js?'.time().'"></script>');
}
	
echo ('</head>'."\n".'<body>'."\n");
$html_out = "";
ob_flush();
flush();

read_config_file();

$config_values['Global']['HTMLOutput']= 1;

//if($_SERVER["REMOTE_ADDR"] == '127.0.0.1') {
if(FALSE) {
	// Basic Interface for Syabas Browser
	// Most of the logic happens in parse_options_local() to send individual pages
	if(isset($_GET['mode'])) {
		parse_options_localhost();
	}
	display_topmenu();
} else {
	// Clutch Style Interface for PC Browsers
	display_progress_bar();
	if(isset($_GET['mode'])) {
		parse_options();
	}
	
	// Main Menu
	display_topmenu();
	display_filter_bar();
	echo $html_out;
	$html_out = "";
	update_progress_bar(5, 'Loading Torrentwatch');
	
	
	// Hidden DIV's
	display_context_menu();
	display_global_config();
	display_favorites();
	display_clear_cache();
	display_hidden_iframe();
	
	echo $html_out;
	$html_out = "";
	update_progress_bar(5, 'Preparing Feeds');

	// Feeds

	load_feeds($config_values['Feeds']);
	feeds_perform_matching($config_values['Feeds']);

	// Comes later incase we just added a torrent	
	display_history();

	hide_progress_bar();
	set_default_div();
}

close_html();
unlink_temp_files();

exit(0);
php?>

