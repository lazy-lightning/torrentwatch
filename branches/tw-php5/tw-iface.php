#!/mnt/syb8634/server/php5-cgi
<?php

ini_set('include_path', '.:/share/.torrents');
$config_file = '/share/.torrents/rss_dl.config';
$test_run = 0;

require_once('rss_dl_utils.php');

function parse_options_local() {
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
				$config_values['Global']['HTMLOutput']= 1;
				if($_GET['feed'] == 'all') {
					load_feeds($config_values['Feeds']);
					feeds_perform_matching($config_values['Feeds']);
				} else {
					$feed[] = $config_values['Feeds'][$_GET['feed']];
					load_feeds($feed);
					feeds_perform_matching($feed);
				}
				unset($config_values['Global']['HTMLOutput']);
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
					$_GET['autostart'] = 'Default';
					update_favorite();
				} else
					$output = "Could not generate Match\n";
				display_favorites();
				break;
			case 'dltorrent':
				client_add_torrent(trim(urldecode($_GET['link'])), $config_values['Settings']['Download Dir']);
				$exit = FALSE;
				break;
			default:
				$output = "Bad Paramaters passed to tw-iface.php";
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

function parse_options_remote() {
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
			case 'showfeed':
				break; // Need to remove all occurances of $exit = TRUE;
				echo $html_out;
				$html_out = "";
				$config_values['Global']['HTMLOutput']= 1;
				if($_GET['feed'] == 'all') {
					load_feeds($config_values['Feeds']);
					feeds_perform_matching($config_values['Feeds']);
				} else {
					$feed[] = $config_values['Feeds'][$_GET['feed']];
					load_feeds($feed);
					feeds_perform_matching($feed);
				}
				unset($config_values['Global']['HTMLOutput']);
				$exit = TRUE;
				break;	
			case 'emptycache':
				$exec = "rm -f ".$config_values['Settings']['Cache Dir']."/*";
				break;
			case 'setglobals':
				$config_values['Settings']['Download Dir']=urldecode($_GET['downdir']);
				$config_values['Settings']['Watch Dir']=urldecode($_GET['watchdir']);
				$config_values['Settings']['Deep Directories']=urldecode($_GET['deepdir']);
				$config_values['Settings']['Verify Episode']=(isset($_GET['verifyepisodes']) ? 1 : 0);
					$config_values['Settings']['Save Torrents']=(isset($_GET['savetorrents']) ? 1 : 0);
				$config_values['Settings']['Client']=urldecode($_GET['client']);
				write_config_file();
				break;
			case 'matchtitle':
				if(($tmp = guess_match(html_entity_decode($_GET['title'])))) {
					$_GET['name'] = $tmp['key'];
					$_GET['filter'] = $tmp['key'];
					$_GET['quality'] = $tmp['data'];
					$_GET['feed'] = $_GET['rss'];
					$_GET['button'] = 'Add';
					$_GET['savein'] = 'Default';
					$_GET['autostart'] = 'Default';
					update_favorite();
				} else
					$output = "Could not generate Match\n";
				break;
			case 'dltorrent':
				client_add_torrent(trim(urldecode($_GET['link'])), $config_values['Settings']['Download Dir']);
				break;
			default:
				$output = "Bad Paramaters passed to tw-iface.php";
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
	$html_out .= '<form action="tw-iface.cgi" id="config_form"><input type="hidden" name="mode" value="setglobals">';
	$html_out .= '<div class="config_torrentclient">';
	$html_out .= '<label class="category">Client Settings</label>';
	$html_out .= '<label class="item">Torrent Client:</label>';
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

	$html_out .= '<div class="config_downloaddir"><label class="item">Download Directory:</label>';
	$html_out .= '<input type="text" name="downdir" value='.$config_values['Settings']['Download Dir'].'></div>';
	$html_out .= '<div class="config_watchdir">';
	$html_out .= '<label class="category">Torrent Settings</label>';
	$html_out .= '<label class="item">Watch Directory:</label>';
	$html_out .= '<input type="text" name="watchdir" value='.$config_values['Settings']['Watch Dir'].'></div>';

	$html_out .= '<div class="config_savetorrent">';
	$html_out .= '<input type="checkbox" name="savetorrents" value=1 ';
	if($config_values['Settings']['Save Torrents'] == 1)
		$html_out .= 'checked=1';
	$html_out .= '><label class="item">Save .torrent</label></div>';

	$html_out .= '<div class="config_deepdir"><label class="item">Deep Directories:</label>';
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

	$html_out .= '<div class="config_verifyepisodes"><label class="category">Favorites Settings</label>';

	$html_out .= '<input type="checkbox" name="verifyepisodes" value=1 ';
	if($config_values['Settings']['Verify Episode'] == 1)
		$html_out .= 'checked=1';
	$html_out .= '><label class="item">Verify Episodes</label>';
	$html_out .= '</div>';
	$html_out .= '<a href="javascript:submitform(\'config_form\')">Save</a>';
	$html_out .= '<a href="javascript:toggleMenu(\'configuration\')">Close</a>';
	$html_out .= '<a href="javascript:toggleMenu(\'feeds\')">Feeds</a>';
	$html_out .= '</form></div>'."\n";

	// Feeds
	$html_out .= '<div class="dialog_window" id="feeds">';
	$html_out .= '<label class="Category">Feeds</label>';
	foreach($config_values['Feeds'] as $key => $feed) {
		$html_out .= '<div class="feeditem">'."\n";
		$html_out .= '<form action="tw-iface.cgi" class="feedform"><input type="hidden" name="mode" value="updatefeed">'."\n";
		$html_out .= '<input type="hidden" name="idx" value="'.$key.'">';
		$html_out .= '<input class="del" type="submit" name="button" value="Delete">'."\n";
	  $html_out .= '<label class="item">'.$feed['Name'].': '.$feed['Link'].'</label></form></div>'."\n";
	}
	$html_out .= '<div class="feeditem">'."\n";
	$html_out .= '<form action="tw-iface.cgi" class="feedform"><input type="hidden" name="mode" value="updatefeed">'."\n";
	$html_out .= '<input type="submit" class="add" name="button" value="Add">';
	$html_out .= '<label class="item">New Feed:</label><input type="text" name="link">'."\n";
	$html_out .= '<a href="javascript:toggleMenu(\'feeds\')">Close</a>';
	$html_out .= '<a href="javascript:toggleMenu(\'configuration\')">Back</a>';
	$html_out .= '</form></div></div>'."\n";

}

function display_favorites_info($item, $key) {
	global $config_values, $html_out;
	$style = "";
	$html_out .= '<div class="FavInfo" id="favorite_'.$key.'" '.$style.'>'."\n";
	$html_out .= '<form action="tw-iface.cgi">'."\n";
	$html_out .= '<input type="hidden" name="mode" value="updatefavorite">'."\n";
	$html_out .= '<input type="hidden" name="idx" value="'.$key.'">'."\n";
	$html_out .= '<div class="favorite_name"><label class="item">Name:</label>';
	$html_out .= '<input type="text" name="name" value="'.$item['Name'].'"></div>'."\n";
	$html_out .= '<div class="favorite_filter"><label class="item">Filter:</label>';
	$html_out .= '<input type="text" name="filter" value="'.$item['Filter'].'"></div>'."\n";
	$html_out .= '<div class="favorite_not"><label class="item">Not:</label>';
	$html_out .= '<input type="text" name="not" value="'.$item['Not'].'"></div>'."\n";
	$html_out .= '<div class="favorite_savein"><label class="item">Save In:</label>';
	$html_out .= '<input type="text" name="savein" value="'.$item['Save In'].'"></div>'."\n";
	$html_out .= '<div class="favorite_episodes"><label class="item">Episodes:</label>';
	$html_out .= '<input type="text" name="episodes" value="'.$item['Episodes'].'"></div>'."\n";
	$html_out .= '<div class="favorite_feed"><label class="item">Feed:</label><select name="feed">'."\n";
	$html_out .= '<option value="all">All</option>'."\n";
	foreach($config_values['Feeds'] as $feed) {
		$html_out .= '<option value="'.urlencode($feed['Link']).'"';
		if($feed['Link'] == $item['Feed'])
			$html_out .= ' selected="selected"';
		$html_out .= '>'.$feed['Name'].'</option>'."\n";
	}
	$html_out .= '</select></div>'."\n";
	$html_out .= '<div class="favorite_quality"><label class="item">Quality:</label>';
	$html_out .= '<input type="text" name="quality" value="'.$item['Quality'].'"></div>'."\n";
	$html_out .= '<div class="favorite_autostart"><label class="item">AutoStart:</label>';
	$html_out .= '<input type="text" name="autostart" value="'.$item['AutoStart'].'"></div>'."\n";
	$html_out .= '<input type="submit" class="add" name="button" value="Update">'."\n";
	$html_out .= '<input type="submit" class="del" name="button" value="Delete">'."\n";
	$html_out .= '<a href="javascript:toggleLayer(\'favorites\')">Close</a></form></div>'."\n";
	// Display the fav that was just updated
	if(isset($_GET['idx']) && $_GET['idx'] == $key) {
		$html_out .= "<script type='text/javascript'>";
		$html_out .= 'toggleFav("favorite_'.$_GET['idx'].'");</script>';
	}
}

function display_favorites() {
	global $config_values, $html_out;
	$html_out .= '<div class="dialog_window" id="favorites">';
	$html_out .= '<div class="Favorite"><ul>';
	foreach($config_values['Favorites'] as $key => $item) {
		$html_out .= '<li><a href="javascript:toggleFav('."'".'favorite_'.$key."'".')">'.$item['Name'].'</a></li>'."\n";
	}
	$html_out .= '<li><a href="javascript:toggleFav(\'favorite_new\')">New Favorite</a></li>'."\n";
	$html_out .= '</ul></div>';
	array_walk($config_values['Favorites'], 'display_favorites_info');
	$item = array('Name' => '', 
	              'Filter' => '', 
	              'Not' => '', 
	              'Save In' => 'Default',
	              'Episodes' => '', 
	              'Feed' => '', 
	              'Quality' => '',
	              'AutoStart' => $config_values['Settings']['AutoStart']);
	display_favorites_info($item, "new");
	$html_out .= '<div class="clear"></div>'."\n";
	$html_out .= '</div>'."\n";
}

function display_options() {
	global $html_out, $config_values;
	$html_out .= '<div class="mainoptions" id="mainoptions">'."\n";
	$html_out .= '<ul>'."\n";
	$html_out .= '<li id="favoritesMenu"><a href="javascript:toggleMenu(\'favorites\');">Favorites</a></li>';
	$html_out .= '<li id="config"><a href="javascript:toggleMenu(\'configuration\');">Configure</a></li>';
	$html_out .= '<li class="divider">&nbsp</li>';
	$html_out .= '<li id="view"><a href="javascript:toggleMenu(\'history\');">View History</a></li>';
	$html_out .= '<li id="divider">&nbsp</li>';
	$html_out .= '<li id="empty"><a href="tw-iface.cgi?mode=emptycache">Empty Cache</a></li>';
	$html_out .= '<li id="inspector"><a href="javascript:toggleMenu(\'inspector\');">Inspector</a></li>';
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
	$history = unserialize(file_get_contents($config_values['Settings']['History']));

	$html_out .= '<div class="dialog_window" id="history"><ul>'."\n";
	$html_tmp = '';
	foreach($history as $item) {
		// add <wbr> tags at dots to help wordwrap
		$item['Title'] = str_replace(".", ".<wbr>", $item['Title']);
		// History is written to file in reverse order
		$html_tmp = '<li>'.$item['Date'].' - '.$item['Title'].'</li>'.$html_tmp;
	}
	$html_out .= $html_tmp;
	$html_out .= '</ul>';
	$html_out .= '<a href="javascript:toggleMenu(\'history\')">Close</a>';
	$html_out .= '<a href="tw-iface.cgi?mode=clearhistory">Clear</a></div>';
}

function display_filter_bar() {
	global $html_out;
	$html_out .= '<div id="filterbar"><ul>';
	$html_out .= '<li id="filter_all"><a href="javascript:filterFeeds(\'all\');">All</a></li>';
	$html_out .= '<li id="filter_matching"><a href="javascript:filterFeeds(\'matching\');">Matching</a></li>';
	$html_out .= '<li id="filter_downloaded"><a href="javascript:filterFeeds(\'downloaded\');">Downloaded</a></li>';
	$html_out .= '</ul></div>'."\n";
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
		case 'setglobals':
			$html_out .= 'toggleMenu(\'configuration\');';
			break;
	}
	$html_out .= '</script>';
}

function close_html() {
	global $html_out, $debug_output;
	$html_out .= "<div class='clear'></div>\n<div class='timer'>Page Took ";
	$time_used = sprintf("%1.4f", timer_get_time());
	$html_out .= $time_used."s to load</div>";
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
<html><head>
<title>Torrentwatch</title>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
<meta http-equiv='expires' content='0'>
<script type="text/javascript" src="tw-iface.js"></script>
<?php
echo ('<link rel="Stylesheet" type="text/css" href="tw-iface');
if($_SERVER["REMOTE_ADDR"] == '127.0.0.1')
	echo ('.local');
echo ('.css?'.time().'"></link>'."\n");
echo ('</head>'."\n".'<body>'."\n");
$html_out = "";

read_config_file();

//if($_SERVER["REMOTE_ADDR"] == '127.0.0.1') {
if(FALSE) {
	// Basic Interface for Syabas Browser
	// Most of the logic happens in parse_options_local() to send individual pages
	if(isset($_GET['mode'])) {
		parse_options_local();
	}
	display_options();
} else {
	// Clutch Style Interface for PC Browsers
	if(isset($_GET['mode'])) {
		parse_options_remote();
	}
	
	// Main Menu
	display_options();
	display_filter_bar();
	
	
	// Hidden DIV's
	display_global_config();
	display_history();
	display_favorites();
	set_default_div();
	
	echo $html_out;
	$html_out = "";
	
	// Feeds
	$config_values['Global']['HTMLOutput']= 1;
	load_feeds($config_values['Feeds']);
	feeds_perform_matching($config_values['Feeds']);
	unset($config_values['Global']['HTMLOutput']);
	
}

close_html();
exit(0);
php?>

