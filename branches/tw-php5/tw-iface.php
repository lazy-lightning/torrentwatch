#!/mnt/syb8634/server/php5-cgi
<?php

ini_set('include_path', '.:/share/.torrents');
$config_file = '/share/.torrents/rss_dl.config';

require_once('rss_dl_utils.php');
function parse_options() {
	global $html_out, $config_values;
	$filler = "<br>";
	$exit = True;

	if(isset($_GET['mode'])) {
		switch($_GET['mode']) {
			case 'config':
				display_global_config();
				break;
			case 'updatefavorite':
				update_favorite();
				$exit = False;
				break;
			case 'updatefeed':
				update_feed();
				display_global_config();
				$exit = True;
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
			case 'viewlog':
				$output = file_get_contents('/var/rss_dl.log');
				break;
			case 'emptycache':
				$exec = "rm -f ".$config_values['Settings']['Cache Dir']."/*";
				$exit = False;
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
				$exit = True;
				break;
			case 'matchtitle':
				if(($tmp = guess_match(html_entity_decode($_GET['title'])))) {
					$_GET['name'] = $tmp['key'];
					$_GET['filter'] = $tmp['key'];
					$_GET['quality'] = $tmp['data'];
					$_GET['feed'] = $_GET['rss'];
					$_GET['button'] = 'Add';
					$_GET['savein'] = 'Default';
					$_GET['autostart'] = $config_values['Settings']['AutoStart'];
					update_favorite();
				} else
					$output = "Could not generate Match\n";
				$exit = False;
				break;
			case 'dltorrent':
				client_add_torrent(trim(urldecode($_GET['link'])));
				$exit = False;
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
	if($exit) {
		$html_out .= "<div class='clear'></div>\n</div></body></html>\n";  
		echo($html_out);
		exit(0);
	}
	return;
}

function display_global_config() {
	global $config_values, $html_out;

	$html_out .= '<div class="configuration"><div class="settings">'."\n";	
	// Settings
	$html_out .= '<form action="tw-iface.cgi"><input type="hidden" name="mode" value="setglobals">';
	$html_out .= 'Global Settings ';
/*	$html_out .= '<input type="image" src="images/add.png" name="optional"></td></tr>'."\n"; */
	$html_out .= '<input class="add" type="submit" value=""><br>'; 
	$html_out .= 'Torrent Client:';
	$html_out .= '<SELECT name="client">';
	$btpd = "";
	$trans = "";
	switch($config_values['Settings']['Client']) {
		case 'btpd':
			$btpd = 'selected="selected"';
			break;
		case 'transmission':
			$trans = 'selected="selected"';
			break;
		default:
			// Shouldn't happen
			break;
	}
	$html_out .= '<option value="btpd" '.$btpd.'>BTPD</option>';
	$html_out .= '<option value="transmission" '.$trans.'>Transmission</option></Select><br>';
	$html_out .= 'Download Directory:';
	$html_out .= '<input type="text" name="downdir" value='.$config_values['Settings']['Download Dir'].'><br>';
	$html_out .= 'Watch Directory:';
	$html_out .= '<input type="text" name="watchdir" value='.$config_values['Settings']['Watch Dir'].'><br>';
	$html_out .= 'Save .torrent:';
	$html_out .= '<input type="checkbox" name="savetorrents" value=1 ';
	if($config_values['Settings']['Save Torrents'] == 1)
		$html_out .= 'checked=1';
	$html_out .= '><br>'."\n";
	$html_out .= 'Deep Directories:';
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
	$html_out .= '<option value="0" '.$tmp3.'>Off</option></select><br>';

	$html_out .= 'Verify Episodes:';
	$html_out .= '<input type="checkbox" name="verifyepisodes" value=1 ';
	if($config_values['Settings']['Verify Episode'] == 1)
		$html_out .= 'checked=1';
	$html_out .= '<br></form></div>'."\n";

	// Feeds
	$html_out .= '<div class="feedconfig">'."\n";
	foreach($config_values['Feeds'] as $key => $feed) {
		$html_out .= '<div class="feeditem">'."\n";
		$html_out .= '<form action="tw-iface.cgi" class="feedform"><input type="hidden" name="mode" value="updatefeed">'."\n";
		$html_out .= '<input type="hidden" name="idx" value="'.$key.'">';
		$html_out .= '<input class="del" type="submit" name="button" value="Delete">'."\n";
	  $html_out .= $feed['Name'].': '.$feed['Link'].'</form></div>'."\n";
	}
	$html_out .= '<div class="feeditem">'."\n";
	$html_out .= '<form action="tw-iface.cgi" class="feedform"><input type="hidden" name="mode" value="updatefeed">'."\n";
	$html_out .= '<input type="submit" class="add" name="button" value="Add">New Feed: <input type="text" name="link">'."\n";
	$html_out .= '</form></div></div></div>'."\n";

}

function display_favorites_info($item, $key) {
	global $config_values, $html_out;
	$html_out .= '<div class="FavInfo" id="favorite_'.$key.'">'."\n";
	$html_out .= 'Filter: ';
	$html_out .= '<input type="text" name="filter" value="'.$item['Filter'].'"><br>'."\n";
	$html_out .= 'Not:';
	$html_out .= '<input type="text" name="not" value="'.$item['Not'].'"><br>'."\n";
	$html_out .= 'Save In: ';
	$html_out .= '<input type="text" name="savein" value="'.$item['Save In'].'"><br>'."\n";
	$html_out .= 'Episodes: ';
	$html_out .= '<input type="text" name="episodes" value="'.$item['Episodes'].'"><br>'."\n";
	$html_out .= 'Feed: <select name="feed">'."\n";
	$html_out .= '<option value="all">All</option>'."\n";
	foreach($config_values['Feeds'] as $feed) {
		$html_out .= '<option value="'.urlencode($feed['Link']).'"';
		if($feed['Link'] == $item['Feed'])
			$html_out .= ' selected="selected"';
		$html_out .= '>'.$feed['Name'].'</option>'."\n";
	}
	$html_out .= '</select><br>'."\n";
	$html_out .= 'Quality: ';
	$html_out .= '<input type="text" name="quality" value="'.$item['Quality'].'"><br>'."\n";
	$html_out .= 'AutoStart: ';
	$html_out .= '<input type="text" name="autostart" value="'.$item['AutoStart'].'"><br></div>'."\n";
}
function display_favorites() {
	global $config_values, $html_out;

	$html_out .= '<div class="Favorites">';
	foreach($config_values['Favorites'] as $key => $item) {
		$html_out .= "<div class='Favorite'>\n";
		$html_out .= '<form action="tw-iface.cgi">'."\n";
		$html_out .= '<input type="hidden" name="mode" value="updatefavorite">'."\n";
		$html_out .= '<input type="hidden" name="idx" value="'.$key.'">'."\n";
		$html_out .= '<div class="FavName"><a href="javascript:toggleLayer(favorite_'.$key.')">'.$item['Name'].'</a><br>'."\n";
		$html_out .= '[ <input type="submit" class="add" name="button" value="Update"> - '."\n";
		$html_out .= '<input type="submit" class="del" name="button" value="Delete"> ]</div>'."\n";
		display_favorites_info($item, $key);
		$html_out .= '</form><div class="clear"></div></div>'."\n";
	}
	unset($item);
	$html_out .= '<div class="Favorite">'."\n";
	$html_out .= '<form action="tw-iface.cgi">'."\n";
	$html_out .= '<input type="hidden" name="mode" value="updatefavorite">'."\n";
	$html_out .= '<div class="FavName">'."\n";
	$html_out .= '<input type="text" name="name" id="newfav" value="New Favorite"><br>'."\n";
	$html_out .= '[ <input type="submit" class="add" name="button" value="Add"> ]</div>'."\n";
	$item = array('Save In' => 'Default', 'AutoStart' => $config_values['Settings']['AutoStart']);
	display_favorites_info($item, "new");
	$html_out .= '</div><div class="clear"></form></div>'."\n";
	$html_out .= '</div>'."\n";
}

function display_feeds() {
	global $config_values, $html_out;
	$html_out .= '<ul>'."\n";
	$html_out .= '<li id="feed"><a href="tw-iface.cgi?mode=showfeed&feed=all">All</a></li>';
	foreach($config_values['Feeds'] as $key => $item) {
		$html_out .= '<li id="feed"><a href="tw-iface.cgi?mode=showfeed&feed='.$key.'">';;
		$html_out .= $item['Name'].'</a></li>';
	}
	$html_out .= '</ul>'."\n";
}

function display_options() {
	global $html_out, $config_values;
	$html_out .= '<ul>'."\n";
	$html_out .= '<li id="favorites"><a href="tw-iface.cgi">Favorites</a></li>';
	$html_out .= '<li id="config"><a href="tw-iface.cgi?mode=config">Configure</a></li>';
	$html_out .= '<li id="view"><a href="tw-iface.cgi?mode=viewlog">View log</a></li>';
	$html_out .= '<li id="empty"><a href="tw-iface.cgi?mode=emptycache">Empty Cache</a></li>';
	if($config_values['Settings']['Client'] == "btpd") {
		$html_out .= '<li id="webui"><a href=http://"';
		if($_SERVER['REMOTE_ADDR'] == "127.0.0.1")
			$html_out .= '127.0.0.1';
		else
			$html_out .= 'popcorn';
		$html_out .= '":8883/torrent/bt.cgi>BitTorrent WebUI</a></li>';
	} else if($config_values['Settings']['Client'] == "transmission")
		$html_out .= '<li id="webui"><a href="http://popcorn:9091/transmission/web/">Transmission</a></li>';
	$html_out .= '</ul>'."\n";
}

//
//
// MAIN Function
//
//
?>
<html><head>
<title>Torrentwatch Configuration Interface</title>
<script type="text/javascript">
// Function from http://www.netlobo.com/div_hiding.html
function toggleLayer( whichLayer )
{
  var elem, vis;
  if ( whichLayer.tagName ) // Added by erik, sometimes ff 3.0.1 passes a reference to the div
    elem = whichLayer;
  else if( document.getElementById ) // this is the way the standards work
    elem = document.getElementById( whichLayer );
  else if( document.all ) // this is the way old msie versions work
      elem = document.all[whichLayer];
  else if( document.layers ) // this is the way nn4 works
    elem = document.layers[whichLayer];
  vis = elem.style;
  // if the style.display value is blank we try to figure it out here
  if(vis.display==''&&elem.offsetWidth!=undefined&&elem.offsetHeight!=undefined)
    vis.display = (elem.offsetWidth!=0&&elem.offsetHeight!=0)?'block':'none';
  vis.display = (vis.display==''||vis.display=='block')?'none':'block';
}
</script>
<meta http-equiv='expires' content='0'>
<?php
echo ('<link rel="Stylesheet" type="text/css" href="tw-iface');
if($_SERVER["REMOTE_ADDR"] == '127.0.0.1')
	echo ('.local');
echo ('.css?'.time().'"></link>'."\n");
echo ('</head>'."\n".'<body><div class="container">'."\n");
$html_out = "";

read_config_file();
$html_out .= '<div class="mainoptions">'."\n";
display_options();
$html_out .= '<hr>';
display_feeds();
$html_out .= '</div>'."\n";
if(isset($_GET['mode'])) {
	parse_options();
	unset($config_values);
	read_config_file();
}
display_favorites();


$html_out .= "<div class='clear'></div>\n</div></body></html>\n";

echo $html_out;

exit(0);

php?>

