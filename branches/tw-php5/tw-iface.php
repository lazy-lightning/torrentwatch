#!/mnt/syb8634/server/php5-cgi
<?php

ini_set('include_path', '.:/share/.torrents');
$config_file = '/share/.torrents/rss_dl.config';

require_once('rss_dl_utils.php');

function parse_options() {
	global $html_out, $config_values;
	$filler = "<br>";
	$exit = True;
	$output = "";

	if(isset($_GET['mode'])) {
		if($_GET['mode'] == 'viewlog') {
			$exec = 'cat /var/rss_dl.log';
		} else if($_GET['mode'] == 'dlnow') {
			$filler = "\n";
			$exec = '/share/.torrents/rss_dl.php -v -D -H';
		} else if($_GET['mode'] == 'emptycache') {
			$exec = "rm -f ".$config_values['Settings']['Cache Dir']."/*";
			$exit = False;
		} else if($_GET['mode'] == 'test') {
			$filler = "\n";
			$exec = '/share/.torrents/rss_dl.php -v -H -t';
		} else if($_GET['mode'] == 'del') {
			$exec = '/opt/sybhttpd/localhost.drives/HARD_DISK/.torrents/rss_dl.php -v -r "'.urldecode($_GET['rss']).'" "'.$_GET['key'].'" "'.$_GET['data'].'"';
			$exit = False;
		} else if ($_GET['mode'] == 'add') {
			$exec = '/opt/sybhttpd/localhost.drives/HARD_DISK/.torrents/rss_dl.php -v -a "'.$_GET['rss'].'" "'.$_GET['key'].'" "'.$_GET['data'].'"';
			$exit = False;
		} else if ($_GET['mode'] == 'setglobals') {
			$config_values['Settings']['Download Dir']=urldecode($_GET['downdir']);
			$config_values['Settings']['Watch Dir']=urldecode($_GET['watchdir']);
			$config_values['Settings']['Deep Directories']=urldecode($_GET['deepdir']);
			$config_values['Settings']['Verify Episode']=(isset($_GET['verifyepisodes']) ? 1 : 0);
			$config_values['Settings']['Save Torrents']=(isset($_GET['savetorrents']) ? 1 : 0);
			$config_values['Settings']['Client']=urldecode($_GET['client']);
			write_config_file();
			$exit = False;
		} else if ($_GET['mode'] == 'matchtitle') {
			if(($tmp = guess_match(html_entity_decode($_GET['title']))))
				$exec = '/opt/sybhttpd/localhost.drives/HARD_DISK/.torrents/rss_dl.php -v -a "'.$_GET['rss'].'" "'.$tmp['key'].'" "'.$tmp['data'].'"';
			else
				$exec = 'echo Could not generate Match';
			$exit = False;
		} else  if ($_GET['mode'] == 'dltorrent') {
			global $config_values;
			if(isset($config_values['Settings']['Cache Dir']))
				$old = $config_values['Settings']['Cache Dir'];
			unset($config_values['Settings']['Cache Dir']);
			echo("<div class='execoutput'>");
			echo("Fetching ".trim(urldecode($_GET['title']))." from ".trim(urldecode($_GET['link']))."<br>");
			fetch_torrent(trim(urldecode($_GET['title'])), trim(urldecode($_GET['link'])));
			update_btcli();
			echo("</div>");
			if(isset($old))
				$config_values['Settings']['Cache Dir'] = $old;
			$exit = False;
			//$html_out .= $html_header;
		} else {
			$html_out .= "<h2>Bad Paramaters passed to tw-iface.php</h2>";
		}
	}

	if(isset($exec)) {
		exec($exec, $output);
		$html_out .= "<div class='execoutput'>".implode($filler, $output)."</div>";
		echo($html_out);
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
	
function display_config() {
	global $config_values, $html_out;

	function rss_callback($item, $key) {
		global $config_values, $html_out;
		$html_out .= "\n";
		if(strcmp($key, 'Settings') == 0 or strcmp($key, 'Global') == 0)
			return;
		//$html_out .= '<tr><td colspan=3>&nbsp;</td></tr>';
		$html_out .= '<tr><td class="feedlink" colspan="3">Feed: <a href="'.$key.'" target="_Blank">'.$key.'</a></td></tr>';
		array_walk($config_values[$key], 'match_callback', $key);
		display_form($key);
	}

	function match_callback($item, $key, $parent) {
		global $config_values, $html_out;
		if($key == "xxOPTIONSxx")
			return;
		$html_out .= "\n";
		$new_rss = my_urlencode($parent);
    
		$html_out .= '<tr><td>'.$key.'</td><td>'.$item.'</td><td>';
		$html_out .= '<form action="tw-iface.cgi">';
		$html_out .= '<input type="hidden" name="mode" value="del">';
		$html_out .= '<input type="hidden" name="rss" value="'.$new_rss.'">';
		$html_out .= '<input type="hidden" name="key" value="'.$key.'">';
		$html_out .= '<input type="hidden" name="data" value="'.$item.'">';
		/* $html_out .= '<input type="image" src="images/del.png" name="optional">'; */
		$html_out .= '&nbsp;<input class="del" type="submit" value="">'; 
		$html_out .= '</form></td></tr>'."\n";
	}


	$html_out .= '<div class="configuration"><table>';
	$html_out .= '<tr style="text-align: center"><td colspan="3"><h2>Current Feed Configuration</h2></td></tr>';
	array_walk($config_values, 'rss_callback');
	display_form();
	display_global_settings();
	$html_out .= "</table></div>";
}

function display_options() {
	global $html_out, $config_values;
	$html_out .= '<div class="mainoptions"><ul>';
	$html_out .= '<li id="config"><a href="tw-iface.cgi">Configure</a></li>';
	$html_out .= '<li id="test"><a href="tw-iface.cgi?mode=test">Test Matches</a></li>';
	$html_out .= '<li id="view"><a href="tw-iface.cgi?mode=viewlog">View log</a></li>';
	$html_out .= '<li id="empty"><a href="tw-iface.cgi?mode=emptycache">Empty Cache</a></li>';
	$html_out .= '<li id="dl"><a href="tw-iface.cgi?mode=dlnow">DL New Torrents</a></li>';
	if($config_values['Settings']['Client'] == "btpd") {
		$html_out .= '<li id="webui"><a href=http://"';
		if($_SERVER['REMOTE_ADDR'] == "127.0.0.1")
			$html_out .= '127.0.0.1';
		else
			$html_out .= 'popcorn';
		$html_out .= '":8883/torrent/bt.cgi>BitTorrent WebUI</a></li>';
	} else if($config_values['Settings']['Client'] == "transmission")
		$html_out .= '<li id="webui"><a href="http://popcorn:8077/index.php5">Clutch</a></li>';
	$html_out .= '</ul></div>';
	echo($html_out);
	$html_out = "";
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
display_options();
if(isset($_GET['mode'])) {
	parse_options();
	unset($config_values);
	read_config_file();
}
display_config();


$html_out .= "<div class='clear'></div>\n</div></body></html>\n";

echo $html_out;

exit(0);

php?>

