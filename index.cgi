#!/usr/bin/php-cgi
<?php

ini_set('include_path', '.:./php');
$test_run = 0;
$firstrun = 0;
$verbosity = 0;

file_put_contents('/tmp/twlog', print_r($_GET, TRUE), FILE_APPEND);
require_once('rss_dl_utils.php');

// This function parses commands sent from a PC browser
function parse_options() {
	global $html_out, $config_values;
	$filler = "<br>";

	if(!isset($_GET['mode']))
		return FALSE;

	switch($_GET['mode']) {
		case 'firstrun':
			if(isset($_GET['link']))
				update_feed();
			if(isset($_GET['client'])) {
				$config_values['Settings']['Client'] = $_GET['client'];
			}
			$config_values['Settings']['FirstRun'] = FALSE;
			write_config_file();
			echo '<meta http-equiv="refresh" content="0;index.html">';
			exit();
			break;
		case 'updatefavorite':
			update_favorite();
			echo '<meta http-equiv="refresh" content="0;index.html">';
			exit();
			break;
		case 'updatefeed':
			update_feed();
			echo '<meta http-equiv="refresh" content="0;index.html">';
			exit();
			break;
		case 'clear_cache':
			clear_cache();
			echo '<meta http-equiv="refresh" content="0;index.html">';
			exit();
			break;
		case 'setglobals':
			update_global_config();
			$config_values['Settings']['FirstRun'] = FALSE;
			write_config_file();
			// This is always called in a hidden frame, so display new config and exit
			display_global_config();
			close_html();
			exit(0);
			break;
		case 'matchtitle':
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
			exit;
			break;
		case 'dltorrent':
			// Dont display full information, this link is loaded in a hidden iframe
			if(stripos($config_values['Settings']['Client'], 'nzb') !== FALSE) 
				$r = client_add_nzb(urldecode($_GET['link']),$_GET['title']);
			else
				$r = client_add_torrent(trim(urldecode($_GET['link'])), $config_values['Settings']['Download Dir']);
			display_history();
			close_html();
			exit(0);
			break;
		case 'clearhistory':
			// Called in hidden div
			if(file_exists($config_values['Settings']['History']))
				unlink($config_values['Settings']['History']);
			display_history();
			close_html();
			exit(0);
			break;
		default:
			$output = "<script type='text/javascript'>alert('Bad Paramaters passed to ".$_SERVER['PHP_SELF']."');</script>";
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
	 '  <h2 class="dialog heading">Configuration</h2>'.
	 '  <form target="update_frame" action="'.$_SERVER['PHP_SELF'].'" id="config_form">'.
	 '    <input type="hidden" name="mode" value="setglobals">'.
	 '    <div id="config_torrentclient">'.
	 '      <label class="category">Client Settings</label>'.
	 '      <label class="item" title="Which torrent client to use">Client:</label>'.
	 '      <select name="client" id="client">'.
	 '        <option value="btpd" '.$btpd.'>BTPD</option>'.
	 '        <option value="transmission1.22" '.$trans122.'>Transmission 1.22</option>'.
	 '        <option value="transmission1.3x" '.$trans13x.'>Transmission &gt;= 1.30</option>'.
	 '        <option value="nzbget" '.$nzbget.'>NZBGet</option>'.
	 '        <option value="sabnzbd" '.$sabnzbd.'>SabNZBd</option>'.
	 '      </select>'.
	 '    </div>'.
	 '    <div id="config_downloaddir" title="Default directory to start items in">'.
	 '      <label class="item">Download Directory:</label>'.
	 '      <input type="text" name="downdir" value='.$config_values['Settings']['Download Dir'].'>'.
	 '    </div>'.
	 '    <div id="config_watchdir">'.
	 '      <label class="category">Torrent Settings</label>'.
	 '      <label class="item" title="Directory to look for new .torrents">Watch Directory:</label>'.
	 '      <input type="text" name="watchdir" value='.$config_values['Settings']['Watch Dir'].'>'.
	 '    </div>'.
	 '    <div id="config_savetorrent">'.
	 '      <input type="checkbox" name="savetorrents" value=1 '.$savetorrent.'>'.
	 '      <label class="item" title="Save index(.torrent or .nzb file) to download directory">Save index files</label>'.
	 '    </div>'.
	 '    <div id="config_deepdir">'.
	 '      <label class="item" title="Save downloads in multi-directory structure">Deep Directories:</label>'.
	 '      <select name="deepdir">'.
	 '        <option value="Full" '.$deepfull.'>Full Name</option>'.
	 '        <option value="Title" '.$deeptitle.'>Show Title</option>'.
	 '        <option value="0" '.$deepoff.'>Off</option>'.
	 '      </select>'.
	 '    </div>'.
	 '    <div id="config_verifyepisodes" title="Try not to download duplicate episodes">'.
	 '      <label class="category">Favorites Settings</label>'.
	 '      <input type="checkbox" name="verifyepisodes" value=1 '.$verifyepisode.'>'.
	 '      <label class="item">Verify Episodes</label>'.
	 '    </div>'.
	 '    <div id="config_onlynewer" title="Only download episodes newer than the last">'.
	 '      <input type="checkbox" name="onlynewer" value=1 '.$onlynewer.'>'.
	 '      <label class="item">Newer Episodes Only</label>'.
	 '    </div>'.
	 '    <div id="config_matchstyle">'.
	 '      <label class="item" title="Type of filter to use">Matching Style:</label>'.
	 '      <select name="matchstyle">'.
	 '        <option value="regexp" '.$matchregexp.'>RegExp</option>'.
	 '        <option value="glob" '.$matchglob.'>Glob</option>'.
	 '        <option value="simple" '.$matchsimple.'>Simple</option>'.
	 '      </select>'.
	 '    </div>'.
	 '   <a id="saveConfig">Save</a>'.
	 "   <a class='toggleDialog' href='#configuration'>Close</a>".
	 "   <a class='toggleDialog' href='#feeds'>Feeds</a>".
         "   <a class='toggleDialog' href='#welcome1'>Wizard</a>".
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
			 '  <form action="'.$_SERVER['PHP_SELF'].'" class="feedform">'.
			 '    <input type="hidden" name="mode" value="updatefeed">'.
			 '    <input type="hidden" name="idx" value="'.$key.'">'.
			 '    <input class="del" type="submit" name="button" value="Delete">'.
			 '    <label class="item">'.$feed['Name'].': '.$feed['Link'].'</label>'.
			 '  </form>'.
			 '</div>';
		}
	}
	$html_out .= 
	 '  <div class="feeditem">'.
	 '    <form action="'.$_SERVER['PHP_SELF'].'" class="feedform">'.
	 '      <input type="hidden" name="mode" value="updatefeed">'.
	 '      <input type="submit" class="add" name="button" value="Add">'.
	 '      <label class="item">New Feed:</label>'.
	 '      <input type="text" name="link">'.
	 '      <a class="toggleDialog" href="#feeds">Close</a>'.
	 '      <a class="toggleDialog" href="#configuration">Back</a>'.
	 '    </form>'.
	 '  </div>'.
	 '</div>';
}

function display_favorites_info($item, $key) {
	global $config_values, $html_out;
	$style = "";
	$html_out .= 
	 '<div class="favinfo" id="favorite_'.$key.'" '.$style.'>'.
	 '  <form action="'.$_SERVER['PHP_SELF'].'">'.
	 '    <input type="hidden" name="mode" value="updatefavorite">'.
	 '    <input type="hidden" name="idx" value="'.$key.'">'.
	 '    <div class="favorite_name">'.
	 '      <label class="item" title="Name of the Favorite">Name:</label>'.
	 '      <input type="text" name="name" value="'.$item['Name'].'">'.
	 '    </div>'.
	 '    <div class="favorite_filter">'.
	 '      <label class="item" title="Regexp filter, use .* to match all">Filter:</label>'.
	 '      <input type="text" name="filter" value="'.$item['Filter'].'">'.
	 '    </div>'.
	 '    <div class="favorite_not">'.
	 '      <label class="item" title="Regexp Not Filter">Not:</label>'.
	 '      <input type="text" name="not" value="'.$item['Not'].'">'.
	 '    </div>'.
	 '    <div class="favorite_savein" id="favorite_savein">'.
	 '      <label class="item" title="Save Directory or Default">Save In:</label>'.
	 '      <input type="text" name="savein" value="'.$item['Save In'].'">'.
	 '    </div>'.
	 '    <div class="favorite_episodes">'.
	 '      <label class="item" title="Regexp Episode filter in form of 2x[1-8]">Episodes:</label>'.
	 '      <input type="text" name="episodes" value="'.$item['Episodes'].'">'.
	 '    </div>'.
	 '    <div class="favorite_feed">'.
	 '      <label class="item" title="Feed to match against">Feed:</label>'.
	 '      <select name="feed">'.
	 '        <option value="all">All</option>';
	if(isset($config_values['Feeds'])) {
		foreach($config_values['Feeds'] as $feed) {
			$html_out .= '<option value="'.urlencode($feed['Link']).'"';
			if($feed['Link'] == $item['Feed'])
				$html_out .= ' selected="selected"';
			$html_out .= '>'.$feed['Name'].'</option>';
		}
	}
	$html_out .= 
	 '      </select>'.
	 '    </div>'.
	 '    <div class="favorite_quality">'.
	 '      <label class="item" title="Regexp Filter against full title">Quality:</label>'.
	 '      <input type="text" name="quality" value="'.$item['Quality'].'">'.
	 '    </div>'.
	 '    <div class="favorite_seedratio" id="favorite_seedratio"><label class="item" title="Maximum seeding ratio, set to -1 to disable">Seed Ratio:</label>'.
	 '      <input type="text" name="seedratio" value="'._isset($item, 'seedRatio', '-1').'">'.
	 '    </div>'.
	 '    <input type="submit" class="add" name="button" value="Update">'.
	 '    <input type="submit" class="del" name="button" value="Delete">'.
	 '    <a class="toggleDialog" href="#favorites">Close</a>'.
	 '  </form>'.
	 '</div>';
}

function display_favorites() {
	global $config_values, $html_out;
	$html_out .= '<div class="dialog_window" id="favorites">'.
	             '<div class="favorite"><ul>'.
	             '<li><a href="#favorite_new">New Favorite</a></li>';
	if(isset($config_values['Favorites'])) {
		foreach($config_values['Favorites'] as $key => $item) {
			$html_out .= '<li><a href="#favorite_'.$key.'">'.$item['Name'].'</a></li>';
		}
	}
	$html_out .= '</ul></div>';
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

function display_topmenu() {
	global $html_out, $config_values;
	$html_out .= 
	 '<div class="mainoptions" id="mainoptions">'.
	 '  <ul>'.
	 '    <li id="refresh"><a href="'.$_SERVER['PHP_SELF'].'">Refresh</a></li>'.
	 '    <li class="divider">&nbsp;</li>'.
	 '    <li id="favoritesMenu"><a class="toggleDialog" href="#favorites">Favorites</a></li>'.
	 '    <li id="config"><a class="toggleDialog" href="#configuration">Configure</a></li>'.
	 '    <li class="divider">&nbsp;</li>'.
	 '    <li id="view"><a class="toggleDialog" href="#history">View History</a></li>'.
	 '    <li id="divider">&nbsp;</li>'.
	 '    <li id="empty"><a class="toggleDialog" href="#clear_cache">Empty Cache</a></li>'.
	 '    <li id="inspector"><a href="#inspector_container">Inspector</a></li>';
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
	}
	$html_out .= '</ul></div>';
}

function display_history() {
	global $html_out, $config_values;

	$html_out .= '<div class="dialog_window" id="history"><div id="historyItems"><ul>';
	if(file_exists($config_values['Settings']['History'])) {
		$history = unserialize(file_get_contents($config_values['Settings']['History']));

		$html_tmp = '';
		foreach($history as $item) {
			// History is written to file in reverse order
			$html_tmp = '<li>'.$item['Date'].' - '.$item['Title'].'</li>'.$html_tmp;
		}
	}
	$html_out .= $html_tmp.'</ul></div>';;
	$html_out .= "<a class='toggleDialog' href='#history'>Close</a>".
                     '<a id="clearhistory" href="'.$_SERVER['PHP_SELF'].'?mode=clearhistory">Clear</a>'.
	             "</div>";
}

function display_clear_cache() {
	global $html_out;
	$html_out .= 
   '<div class="dialog_window" id="clear_cache">'.
	 '  <h2 class="dialog heading">Which Cache</h2>'.
	 '  <a class="toggleDialog" href="#clear_cache">Close</a>'.
	 '  <a href="'.$_SERVER['PHP_SELF'].'?mode=clear_cache&type=feeds">Feeds</a>'.
	 '  <a href="'.$_SERVER['PHP_SELF'].'?mode=clear_cache&type=torrents">Torrents</a>'.
	 '  <a href="'.$_SERVER['PHP_SELF'].'?mode=clear_cache&type=all">All</a>'.
	 '</div>';
}

function display_filter_bar() {
	global $html_out;
	$html_out .= 
	 '<div id="filterbar_container">'.
	 '  <ul id="filterbar">'.
	 '    <li id="filter_all"><a href="#filter_all">All</a></li>'.
	 '    <li id="filter_matching"><a href="#filter_matching">Matching</a></li>'.
	 '    <li id="filter_downloaded"><a href="#filter_downloaded">Downloaded</a></li>'.
	 '  </ul>'.
	 '  <input type="text" id="filter_text_input">'.
	 '</div>';
}

function display_progress_bar() {
  global $html_out;
  $html_out .= <<<EOD
<div class="progressbar dialog_window" id="progressbar">
 <span>Loading . . .</span>
</div>
EOD;

}

function display_context_menu() {
	global $html_out;
	$html_out .= '<div class="context_menu" id="CM1"><ul>'.
	 '<li id="addToFavorites">Add to Favorites</li>'.
	 '<li id="startDownloading">Start Downloading</li>'.
	 '<li id="inspect">Inspect</li>'.
	 '</ul></div>';
}
	
function display_hidden_iframe() {
	global $html_out;
	$html_out .= '<iframe id="update_frame" src="about:blank" name="update_frame"></iframe>';
}

function display_inspector() {
	global $html_out;
	$html_out .= '
<div id="inspector_container">
  <div id="inspector_loading"></div>
  <div id="inspector_info"></div>
</div>';
}

function set_default_div() {
	global $html_out, $config_values;
	return;

	if(!isset($_GET['mode']) && $config_values['Settings']['FirstRun'] == FALSE)
		return;

	$html_out .= '<script type="text/javascript">';
	if($config_values['Settings']['FirstRun'] == TRUE)
		$html_out .= 'toggleMenu(\'welcome1\');';
	else switch($_GET['mode']) {
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
	$html_out .= '</script>';
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

if(isset($_GET['mode'])) {
	parse_options();
}

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
