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
				$r = client_add_torrent(trim(urldecode($_GET['link'])), $config_values['Settings']['Download Dir']);
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
	$onlynewer="";

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

	$html_out .= 
	 '<div class="dialog_window" id="configuration">'.
	 '  <h2 class="dialog_heading">Configuration</h2>'.
	 '  <form target="update_frame" action="'.$_SERVER['PHP_SELF'].'/setGlobals" id="config_form">'.
	 '    <label class="category">Client Settings</label>'.
	 '    <div id="config_torrentclient">'.
	 '      <label class="item select" title="Which torrent client to use">Client:</label>'.
	 '      <select name="client" id="client">'.
	 '        <option value="btpd" '.$btpd.'>BTPD</option>'.
	 '        <option value="transmission1.22" '.$trans122.'>Transmission 1.22</option>'.
	 '        <option value="transmission1.3x" '.$trans13x.'>Transmission &gt;= 1.30</option>'.
	 '        <option value="nzbget" '.$nzbget.'>NZBGet</option>'.
	 '        <option value="sabnzbd" '.$sabnzbd.'>SabNZBd</option>'.
	 '      </select>'.
	 '    </div>'.
	 '    <div id="config_downloaddir" title="Default directory to start items in">'.
	 '      <label class="item textinput">Download Directory:</label>'.
	 '      <input type="text" name="downdir" value='.$config_values['Settings']['Download Dir'].'>'.
	 '    </div>'.
	 '    <label class="category" id="torrent_settings">Torrent Settings</label>'.
	 '    <div id="config_watchdir">'.
	 '      <label class="item textinput" title="Directory to look for new .torrents">Watch Directory:</label>'.
	 '      <input type="text" name="watchdir" value='.$config_values['Settings']['Watch Dir'].'>'.
	 '    </div>'.
	 '    <div id="config_savetorrent">'.
	 '      <input type="checkbox" name="savetorrents" value=1 '.$savetorrent.'>'.
	 '      <label class="item checkbox" title="Save index(.torrent or .nzb file) to download directory">Save index files</label>'.
	 '    </div>'.
	 '    <div id="config_deepdir">'.
	 '      <label class="item select" title="Save downloads in multi-directory structure">Deep Directories:</label>'.
	 '      <select name="deepdir">'.
	 '        <option value="Full" '.$deepfull.'>Full Name</option>'.
	 '        <option value="Title" '.$deeptitle.'>Show Title</option>'.
	 '        <option value="0" '.$deepoff.'>Off</option>'.
	 '      </select>'.
	 '    </div>'.
	 '    <label class="category">Favorites Settings</label>'.
	 '    <div id="config_verifyepisodes" title="Try not to download duplicate episodes">'.
	 '      <input type="checkbox" name="verifyepisodes" value=1 '.$verifyepisode.'>'.
	 '      <label class="item checkbox">Verify Episodes</label>'.
	 '    </div>'.
	 '    <div id="config_onlynewer" title="Only download episodes newer than the last">'.
	 '      <input type="checkbox" name="onlynewer" value=1 '.$onlynewer.'>'.
	 '      <label class="item checkbox">Newer Episodes Only</label>'.
	 '    </div>'.
	 '    <div id="config_matchstyle">'.
	 '      <label class="item select" title="Type of filter to use">Matching Style:</label>'.
	 '      <select name="matchstyle">'.
	 '        <option value="regexp" '.$matchregexp.'>RegExp</option>'.
	 '        <option value="glob" '.$matchglob.'>Glob</option>'.
	 '        <option value="simple" '.$matchsimple.'>Simple</option>'.
	 '      </select>'.
	 '    </div>'.
         '    <div class="buttonContainer">'.
	 '      <a class="submitForm button" id="Save" href="#">Save</a>'.
	 "      <a class='toggleDialog button' href='#configuration'>Close</a>".
	 "      <a class='toggleDialog button' href='#feeds'>Feeds</a>".
         "      <a class='toggleDialog button' href='#welcome1'>Wizard</a>".
         '    </div>'.
	 '  </form>'.
	 '</div>';

	// Feeds
	$html_out .= 
	 '<div class="dialog_window" id="feeds">'.
	 '  <h2 class="dialog_heading">Feeds</h2>';
	if(isset($config_values['Feeds'])) {
		foreach($config_values['Feeds'] as $key => $feed) {
			$html_out .= 
			 '<div class="feeditem">'.
			 '  <form action="'.$_SERVER['PHP_SELF'].'/updateFeed" class="feedform">'.
			 '    <input type="hidden" name="idx" value="'.$key.'">'.
                         '    <a class="submitForm button" id="Delete" href="#">Delete</a>'.
			 '    <label class="item">'.$feed['Name'].': '.$feed['Link'].'</label>'.
			 '  </form>'.
			 '</div>';
		}
	}
	$html_out .= 
	 '  <div class="feeditem">'.
	 '    <form action="'.$_SERVER['PHP_SELF'].'/updateFeed" class="feedform">'.
         '      <a class="submitForm button" id="Add" href="#">Add</a>'.
	 '      <label class="item">New Feed:</label>'.
	 '      <input type="text" name="link">'.
	 '      <a class="toggleDialog button" href="#feeds">Close</a>'.
	 '      <a class="toggleDialog button" href="#configuration">Back</a>'.
	 '    </form>'.
	 '  </div>'.
	 '</div>';
}

function display_favorites_info($item, $key) {
	global $config_values, $html_out;
	$style = "";
	$html_out .= 
	 '<form action="'.$_SERVER['PHP_SELF'].'/updateFavorite" class="favinfo" id="favorite_'.$key.'" '.$style.'>'.
	 '  <input type="hidden" name="idx" id="idx" value="'.$key.'">'.
	 '  <div class="favorite_name">'.
	 '    <label class="item" title="Name of the Favorite">Name:</label>'.
	 '    <input type="text" name="name" value="'.$item['Name'].'">'.
	 '  </div>'.
	 '  <div class="favorite_filter">'.
	 '    <label class="item" title="Regexp filter, use .* to match all">Filter:</label>'.
	 '    <input type="text" name="filter" value="'.$item['Filter'].'">'.
	 '  </div>'.
	 '  <div class="favorite_not">'.
	 '    <label class="item" title="Regexp Not Filter">Not:</label>'.
	 '    <input type="text" name="not" value="'.$item['Not'].'">'.
	 '  </div>'.
	 '  <div class="favorite_savein" id="favorite_savein">'.
	 '    <label class="item" title="Save Directory or Default">Save In:</label>'.
	 '    <input type="text" name="savein" value="'.$item['Save In'].'">'.
	 '  </div>'.
	 '  <div class="favorite_episodes">'.
	 '    <label class="item" title="Regexp Episode filter in form of 2x[1-8]">Episodes:</label>'.
	 '    <input type="text" name="episodes" value="'.$item['Episodes'].'">'.
	 '  </div>'.
	 '  <div class="favorite_feed">'.
	 '    <label class="item" title="Feed to match against">Feed:</label>'.
	 '    <select name="feed">'.
	 '      <option value="all">All</option>';
	if(isset($config_values['Feeds'])) {
		foreach($config_values['Feeds'] as $feed) {
			$html_out .= '<option value="'.urlencode($feed['Link']).'"';
			if($feed['Link'] == $item['Feed'])
				$html_out .= ' selected="selected"';
			$html_out .= '>'.$feed['Name'].'</option>';
		}
	}
	$html_out .= 
	 '    </select>'.
	 '  </div>'.
	 '  <div class="favorite_quality">'.
	 '    <label class="item" title="Regexp Filter against full title">Quality:</label>'.
	 '    <input type="text" name="quality" value="'.$item['Quality'].'">'.
	 '  </div>'.
	 '  <div class="favorite_seedratio"><label class="item" title="Maximum seeding ratio, set to -1 to disable">Seed Ratio:</label>'.
	 '    <input type="text" name="seedratio" value="'._isset($item, 'seedRatio', '-1').'">'.
	 '  </div>'.
	 '  <div class="buttonContainer">'.
         '    <a class="submitForm button" id="Update" href="#">Update</a>'.
         '    <a class="submitForm button" id="Delete" href="#">Delete</a>'.
	 '    <a class="toggleDialog button" href="#favorites">Close</a>'.
	 '  </div>'.
	 '</form>';
}

function display_favorites() {
	global $config_values, $html_out;
	$html_out .= '<div class="dialog_window" id="favorites">'.
	             '<ul class="favorite">'.
	             '<li><a href="#favorite_new">New Favorite</a></li>';
	if(isset($config_values['Favorites'])) {
		foreach($config_values['Favorites'] as $key => $item) {
			$html_out .= '<li><a href="#favorite_'.$key.'">'.$item['Name'].'</a></li>';
		}
	}
	$html_out .= '</ul>';
	if(isset($config_values['Favorites']))
		array_walk($config_values['Favorites'], 'display_favorites_info');
	$item = array('Name' => '', 
	              'Filter' => '', 
	              'Not' => '', 
	              'Save In' => 'Default',
	              'Episodes' => '', 
	              'Feed' => '', 
	              'Quality' => 'All');
	display_favorites_info($item, "new");
	$html_out .= '<div class="clear"></div>'.
	             '</div>';
}

/* Needs to be re-integrated somewhere, javascript?
	if($_SERVER['REMOTE_ADDR'] == "127.0.0.1")
		$host = '127.0.0.1';
	else if(!empty($_SERVER['SERVER_NAME']))
		$host = $_SERVER['SERVER_NAME'];
	else
		$host = platform_getHostname();

	switch($config_values['Settings']['Client']) {
		case 'btpd':
			$html_out .= "<li id='webui'><a href='http://$host:8883/torrent/bt.cgi'>BitTorrent WebUI</a></li>";
			break;
		case 'transmission1.3x':
		case 'transmission1.32':
			$html_out .= "<li id='webui'><a href='http://$host:9091/transmission/web/'>Transmission</a></li>";
			break;
		case 'transmission1.22':
			$html_out .= "<li id='webui'><a href='http://$host:8077/'>Clutch</a></li>";
			break;
		case 'nzbget':
			$html_out .= "<li id='webui'><a href='http://$host:8066/'>NZB</a></li>";
			break;
		case 'sabnzbd':
			$html_out .= "<li id='webui'><a href='http://$host:8080/sabnzbd/'>SabNZBd</a></li>";
			break;
	} */

function display_history() {
	global $html_out, $config_values;

	$html_out .= '<div class="dialog_window" id="history"><ul id="historyItems">';
	if(file_exists($config_values['Settings']['History'])) {
		$history = unserialize(file_get_contents($config_values['Settings']['History']));

		$html_tmp = '';
		foreach($history as $item) {
			// History is written to file in reverse order
			$html_tmp = '<li>'.$item['Date'].' - '.$item['Title'].'</li>'.$html_tmp;
		}
	}
	$html_out .= $html_tmp.'</ul>';;
	$html_out .= "<a class='toggleDialog button' href='#history'>Close</a>".
                     '<a class="button" id="clearhistory" href="'.$_SERVER['PHP_SELF'].'/clearHistory">Clear</a>'.
	             "</div>";
}

function display_clear_cache() {
	global $html_out;
	$html_out .= 
   '<div class="dialog_window" id="clear_cache">'.
	 '  <h2 class="dialog_heading">Which Cache</h2>'.
	 '  <a class="toggleDialog" href="#clear_cache">Close</a>'.
	 '  <a href="'.$_SERVER['PHP_SELF'].'/clearCache?type=feeds">Feeds</a>'.
	 '  <a href="'.$_SERVER['PHP_SELF'].'/clearCache?type=torrents">Torrents</a>'.
	 '  <a href="'.$_SERVER['PHP_SELF'].'/clearCache?type=all">All</a>'.
	 '</div>';
}

function close_html() {
	global $html_out, $debug_output, $main_timer;
	$debug_output .= $verbosity;
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

// Main Menu
//display_topmenu();
//display_filter_bar();
//$html_out .= file_get_contents('html/welcome.html');

//display_progress_bar();
//echo $html_out;
//ob_flush();flush();
//$html_out = "";


// Hidden DIV's
//display_context_menu();
display_global_config();
display_favorites();
//display_clear_cache();
//display_hidden_iframe();

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
//display_inspector();

//set_default_div();

close_html();
unlink_temp_files();

exit(0);
php?>

