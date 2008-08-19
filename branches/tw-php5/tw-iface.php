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
			case 'dlnow':
				$filler = "\n";
				$exec = '/share/.torrents/rss_dl.php -v -D -H';
				break;
			case 'emptycache':
				$exec = "rm -f ".$config_values['Settings']['Cache Dir']."/*";
				$exit = False;
				break;
			case 'test':
				$filler = "\n";
				$exec = '/share/.torrents/rss_dl.php -v -H -t';
				break;
			case 'del':
				$output = "Removing ".$_GET['key']." = ".$_GET['data']." from ".urldecode($_GET['rss']);
				update_config_real(RSS_DEL, urldecode($_GET['rss']), $_GET['key'], $_GET['data']);
				$exit = False;
				break;
			case 'add':
				$output = "Adding ".$_GET['key']." = ".$_GET['data']." to ".urldecode($_GET['rss']);
				update_config_real(RSS_ADD, urldecode($_GET['rss']), $_GET['key'], $_GET['data']);
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
				$exit = False;
				break;
			case 'matchtitle':
				if(($tmp = guess_match(html_entity_decode($_GET['title'])))) {
					$output = "Adding ".$tmp['key']." = ".$tmp['data']." To ".urldecode($_GET['rss']);
					update_config_real(RSS_ADD, urldecode($_GET['rss']), $tmp['key'], $tmp['data']);
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

function display_form($rss = FALSE) {
	global $html_out;
	$html_out .= '<form action="tw-iface.cgi"><input type="hidden" name="mode" value="add">';
	if($rss) {
		$html_out .= '<input type="hidden" name="rss" value="'.$rss.'">';
	} else {
		$html_out .= '<tr><td colspan=3>&nbsp;</td></tr>';
		$html_out .= '<tr style="text-align: center"><td colspan="2">';
		$html_out .= 'New RSS Feed</td></tr><tr><td colspan="2">';
		$html_out .= '<center><label>Feed: <input type="text" name="rss"></center></td></tr>';
	} 
	$html_out .= '<tr><td>';
	$html_out .= '<input type="text" name="key"></td><td><input type="text" name="data"></td>';
/*	$html_out .= '<td><input type="image" src="images/add.png" name="optional"></td>'; */
	$html_out .= '<td>&nbsp;<input class="add" type="submit" Value=""></td>'; 
	if($rss) {	
		$html_out .= '<tr><td class="feedlink" colspan="3">&nbsp;</td></tr>';
		$html_out .= '<tr><td><br />&nbsp;<br /></td></tr>';
	}
	$html_out .= '</form>';
}

function display_global_settings() {
	global $config_values, $html_out;

	$html_out .= "\n";	
	$html_out .= '<tr><td colspan=2>&nbsp;</td></tr>';
	$html_out .= '<form action="tw-iface.cgi"><input type="hidden" name="mode" value="setglobals">';
	$html_out .= '<tr><td colspan=2 style="text-align: center;">Global Settings ';
/*	$html_out .= '<input type="image" src="images/add.png" name="optional"></td></tr>'."\n"; */
	$html_out .= '<input class="add" type="submit" value=""></td>'; 

	$html_out .= '<tr>';
	$html_out .= '<td style="text-align: right;">Torrent Client:</td>';
	$html_out .= '<td><SELECT name="client">';
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
	$html_out .= '<option value="transmission" '.$trans.'>Transmission</option></Select></td>';
	$html_out .= '</td></tr>'."\n";

	$html_out .= '<tr>';
	$html_out .= '<td style="text-align: right;">Download Directory:</td>';
	$html_out .= '<td><input type="text" name="downdir" value='.$config_values['Settings']['Download Dir'].'></td>';
	$html_out .= '</td></tr>'."\n";
	$html_out .= '<tr><td style="text-align: right;">Watch Directory:</td>';
	$html_out .= '<td><input type="text" name="watchdir" value='.$config_values['Settings']['Watch Dir'].'></td>';
	$html_out .= '</td></tr>'."\n";


	$html_out .= '<tr><td style="text-align: right;">Save .torrent:</td>';
	$html_out .= '<td><input type="checkbox" name="savetorrents" value=1 ';
	if($config_values['Settings']['Save Torrents'] == 1)
		$html_out .= 'checked=1';
	$html_out .= '></td></tr>'."\n";

	$html_out .= '<tr><td style="text-align: right;">Deep Directories:</td>';
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
	$html_out .= '<td><select name="deepdir">';
	$html_out .= '<option value="Full" '.$tmp1.'>Full Name</option>';
	$html_out .= '<option value="Title" '.$tmp2.'>Show Title</option>';
	$html_out .= '<option value="0" '.$tmp3.'>Off</option></select></td></tr>';

	$html_out .= '<tr><td style="text-align: right;">Verify Episodes:</td>';
	$html_out .= '<td><input type="checkbox" name="verifyepisodes" value=1 ';
	if($config_values['Settings']['Verify Episode'] == 1)
		$html_out .= 'checked=1';
	$html_out .= '></td></tr></form>'."\n";

}

function display_favorites_info($item) {
	global $config_values, $html_out;
	$html_out .= '<div class="FavInfo">Filter: ';
	$html_out .= '<input type="text" name="filter" value="'.$item['Filter'].'"><br>';
	$html_out .= 'Not: ';
	$html_out .= '<input type="text" name="not" value="'.$item['Not'].'"><br>';
	$html_out .= 'Save In: ';
	$html_out .= '<input type="text" name="savein" value="'.$item['Save In'].'"><br>';
	$html_out .= 'Episodes: ';
	$html_out .= '<input type="text" name="episodes" value="'.$item['Episodes'].'"><br>';
	$html_out .= 'Feed: <select name="feed">';
	$html_out .= '<option value="all">All</option>';
	foreach($config_values['Feeds'] as $feed) {
		$html_out .= '<option value="'.urlencode($feed['Link']).'"';
		if($feed['Link'] == $item['Feed'])
			$html_out .= ' selected="selected"';
		$html_out .= '>'.$feed['Name'].'</option>';
	}
	$html_out .= '</select>';
	$html_out .= 'Quality: ';
	$html_out .= '<input type="text" name="quality" value="'.$item['Quality'].'"><br>';
	$html_out .= 'AutoStart: ';
	$html_out .= '<input type="text" name="autostart" value="'.$item['AutoStart'].'"><br></div>'."\n";
}
function display_favorites() {
	global $config_values, $html_out;

	$html_out .= '<div class="configuration">';
	foreach($config_values['Favorites'] as $key => $item) {
		$html_out .= '<div class="Favorite">';
		$html_out .= '<form action="tw-iface.php">';
		$html_out .= '<input type="hidden" mode="addfavorite">';
		$html_out .= '<input type="hidden" name="fav" value="'.$key.'">';
		$html_out .= '<div class="FavName">'.$key.'<br>';
		$html_out .= '[ <input type="submit" value="Update"> - ';
		$html_out .= '<input type="submit" value="Delete"> ]</div>';
		display_favorites_info($item);
		$html_out .= '</div><div class="clear"></form></div>'."\n";
	}
	unset($item);
	$html_out .= '<div class=Favorite">';
	$html_out .= '<form action=tw-iface.php">';
	$html_out .= '<input type="hidden" mode="addfavorite">';
	$html_out .= '<div class="FavName">';
	$html_out .= '<input type=text" name="fav" value="New Favorite"><br>';
	$html_out .= '[ <input type="submit" value="Add"> ]</div>';
	$item['Save In'] = 'Default';
	display_favorites_info($item);
	$html_out .= '</div><div class="clear"></form></div>'."\n";
	$html_out .= '</div>';
}

function display_feeds() {
	global $config_values, $html_out;
	$html_out .= '<ul>';
	$html_out .= '<li id="feed"><a href="tw-iface.cgi?mode=showfeed&feed=all">All</a></li>';
	foreach($config_values['Feeds'] as $key => $item) {
		$html_out .= '<li id="feed"><a href="tw-iface.cgi?mode=showfeed&feed='.$key.'">';;
		$html_out .= $item['Name'].'</a></li>';
	}
	$html_out .= '</ul>'."\n";
}

function display_options() {
	global $html_out, $config_values;
	$html_out .= '<ul>';
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
	$html_out .= '</ul>';
}

//
//
// MAIN Function
//
//

echo ("<html><head><title>Torrentwatch Configuration Interface</title>\n");
echo ("<meta http-equiv='expires' content='0'>\n");
echo ('<link rel="Stylesheet" type="text/css" href="tw-iface');
if($_SERVER["REMOTE_ADDR"] == '127.0.0.1')
	echo ('.local');
echo ('.css?'.time().'"></link>'."\n");
echo ('</head>'."\n".'<body><div class="container">');
$html_out = "";

read_config_file();
$html_out .= '<div class="mainoptions">';
display_options();
$html_out .= '<hr>';
display_feeds();
$html_out .= '</div>';
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

