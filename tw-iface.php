#!/mnt/syb8634/server/php
<?php

ini_set('include_path', '.:/share/.torrents');
$config_file = '/share/.torrents/rss_dl.config';
$download_dir_config = '/share/.torrents/download_dir.config';

require_once('rss_dl_utils.php');

function parse_options() {
	global $argc, $argv, $html_out, $config_values, $download_dir_config;
	$opts = explode("&", $argv[1]);
	$filler = "<br />";
	$exit = True;
	$output = "";

	foreach($opts as $i) {
		$piece = explode("=", $i); 
		$cmdline[$piece[0]] = urldecode($piece[1]);
	}

	if(isset($cmdline['mode'])) {
		if(strcmp($cmdline['mode'], 'viewlog') == 0) {
			$exec = 'cat /var/rss_dl.log';
		} else if(strcmp($cmdline['mode'], 'dlnow') == 0) {
			$filler = "\n";
			$exec = '/share/.torrents/rss_dl.php -v -D -H';
		} else if(strcmp($cmdline['mode'], 'emptycache') == 0) {
			read_config_file();
			$exec = "rm -f ".$config_values['Settings']['Cache Dir']."/*";
			$exit = False;
		} else if(strcmp($cmdline['mode'], 'test') == 0) {
			$filler = "\n";
			$exec = '/share/.torrents/rss_dl.php -v -H -t';
		} else if(strcmp($cmdline['mode'], 'del') == 0) {
			$exec = '/opt/sybhttpd/localhost.drives/HARD_DISK/.torrents/rss_dl.php -v -r "'.urldecode($cmdline['rss']).'" "'.$cmdline['key'].'" "'.$cmdline['data'].'"';
			$exit = False;
		} else if (strcmp($cmdline['mode'], 'add') == 0) {
			$exec = '/opt/sybhttpd/localhost.drives/HARD_DISK/.torrents/rss_dl.php -v -a "'.$cmdline['rss'].'" "'.$cmdline['key'].'" "'.$cmdline['data'].'"';
			$exit = False;
		} else if (strcmp($cmdline['mode'], 'setglobals') == 0) {
			read_config_file();
			$config_values['Settings']['Download Dir']=urldecode($cmdline['downdir']);
			$config_values['Settings']['Watch Dir']=urldecode($cmdline['watchdir']);
			$config_values['Settings']['Deep Directories']=(isset($cmdline['deepdir']) ? 1 : 0);
			$config_values['Settings']['Verify Episode']=(isset($cmdline['verifyepisodes']) ? 1 : 0);
			$config_values['Settings']['Save Torrents']=(isset($cmdline['savetorrents']) ? 1 : 0);
			write_config_file();
			$exit = False;
		} else if (strcmp($cmdline['mode'], 'matchtitle') == 0) {
			if(($tmp = guess_match(html_entity_decode($cmdline['title']))))
				$exec = '/opt/sybhttpd/localhost.drives/HARD_DISK/.torrents/rss_dl.php -v -a "'.$cmdline['rss'].'" "'.$tmp['key'].'" "'.$tmp['data'].'"';
			else
				$exec = 'echo Could not generate Match';
			$exit = False;
		} else  if (strcmp($cmdline['mode'], 'dltorrent') == 0) {
			global $config_values;
			read_config_file();
			if(isset($config_values['Settings']['Cache Dir']))
				$old = $config_values['Settings']['Cache Dir'];
			unset($config_values['Settings']['Cache Dir']);
			echo("<div class='execoutput'>");
			echo("Fetching ".trim(urldecode($cmdline['title']))." from ".trim(urldecode($cmdline['link']))."<br>");
			fetch_torrent(trim(urldecode($cmdline['title'])), trim(urldecode($cmdline['link'])));
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

	if($exec) {
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
	$html_out .= '<td><input type="image" src="images/add.png" name="optional"></td>';
	/* $html_out .= '<td><input class="add" type="submit" value="Add"></td></tr>'; */
	if($rss) {	
		$html_out .= '<tr><td class="feedlink" colspan="3">&nbsp;</td></tr>';
		$html_out .= '<tr><td><br />&nbsp;<br /></td></tr>';
	}
	$html_out .= '</form>';
}

function display_global_settings() {
	global $config_values, $html_out;
	
	$html_out .= '<tr><td colspan=2>&nbsp;</td></tr>';
  $html_out .= '<form action="tw-iface.cgi"><input type="hidden" name="mode" value="setglobals">';
	$html_out .= '<tr><td colspan=2 style="text-align: center;">Global Settings ';
	$html_out .= '<input type="image" src="images/add.png" name="optional"></td></tr>';

	$html_out .= '<tr>';
	$html_out .= '<td style="text-align: right;">Download Directory:</td>';
	$html_out .= '<td><input type="text" name="downdir" value='.$config_values['Settings']['Download Dir'].'></td>';
	$html_out .= '</td></tr>';

	$html_out .= '<tr><td style="text-align: right;">Watch Directory:</td>';
	$html_out .= '<td><input type="text" name="watchdir" value='.$config_values['Settings']['Watch Dir'].'></td>';
	$html_out .= '</td></tr>';


	$html_out .= '<tr><td style="text-align: right;">Save .torrent:</td>';
	$html_out .= '<td><input type="checkbox" name="savetorrents" value=1 ';
	if($config_values['Settings']['Save Torrents'] == 1)
		$html_out .= 'checked=1';
	$html_out .= '></td></tr>';

	$html_out .= '<tr><td style="text-align: right;">Deep Directories:</td>';
	$html_out .= '<td><input type="checkbox" name="deepdir" value=1 ';
	if($config_values['Settings']['Deep Directories'] == 1)
		$html_out .= 'checked=1';
	$html_out .= '></td></tr>';
	$html_out .= '<tr><td style="text-align: right;">Verify Episodes:</td>';
	$html_out .= '<td><input type="checkbox" name="verifyepisodes" value=1 ';
	if($config_values['Settings']['Verify Episode'] == 1)
		$html_out .= 'checked=1';
	$html_out .= '></td></tr></form>';

}
	
function display_config() {
	global $config_values, $html_out;

	function rss_callback($item, $key) {
		global $config_values, $html_out;
		$html_out .= "\n";
		if(strcmp($key, 'Settings') == 0)
			return;
		//$html_out .= '<tr><td colspan=3>&nbsp;</td></tr>';
		$html_out .= '<tr><td class="feedlink" colspan="3">Feed: <a href="'.$key.'" target="_Blank">'.$key.'</a></td></tr>';
		array_walk($config_values[$key], 'match_callback', $key);
		display_form($key);
	}

	function match_callback($item, $key, $parent) {
		global $config_values, $html_out;
		$html_out .= "\n";
		$new_rss = my_urlencode($parent);
    
		$html_out .= '<tr><td>'.$key.'</td><td>'.$item.'</td><td>';
		$html_out .= '<form action="tw-iface.cgi">';
		$html_out .= '<input type="hidden" name="mode" value="del">';
		$html_out .= '<input type="hidden" name="rss" value="'.$new_rss.'">';
		$html_out .= '<input type="hidden" name="key" value="'.$key.'">';
		$html_out .= '<input type="hidden" name="data" value="'.$item.'">';
		$html_out .= '<input type="image" src="images/del.png" name="optional">';
		/* $html_out .= '<input class="del" type="submit" value="Delete">'; */
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
	global $html_out;
	$html_out .= '<div class="mainoptions"><ul>';
	$html_out .= '<li id="config"><a href="tw-iface.cgi">Configure</a></li>';
	$html_out .= '<li id="test"><a href="tw-iface.cgi?mode=test">Test Matches</a></li>';
	$html_out .= '<li id="view"><a href="tw-iface.cgi?mode=viewlog">View log</a></li>';
	$html_out .= '<li id="empty"><a href="tw-iface.cgi?mode=emptycache">Empty Cache</a></li>';
	$html_out .= '<li id="dl"><a href="tw-iface.cgi?mode=dlnow">DL New Torrents</a></li>';
	$html_out .= '<li id="webui"><a href="http://popcorn:8883/torrent/bt.cgi">BitTorrent WebUI</a></li>';
	$html_out .= '</ul></div>';
	echo($html_out);
	$html_out = "";
}

//
//
// MAIN Function
//
//

echo "Content-Type: text/html\n";
echo "\n";
echo ("<html><head><title>Torrentwatch Configuration Interface</title>");
echo ("<meta http-equiv='expires' content='0'>");
if(getenv("REMOTE_ADDR") == '127.0.0.1')
	echo ('<link rel="StyleSheet" type="text/css" href="tw-iface.local.css?'.time().'"></link>');
else
	echo ('<link rel="StyleSheet" type="text/css" href="tw-iface.css?'.time().'"></link>');
echo ("</head><body><div class='container'>");
echo 
$html_out = "";

display_options();
if($argv[1])
	parse_options();
read_config_file();
display_config();


$html_out .= "<div class='clear'></div>\n</div></body></html>\n";

echo $html_out;

exit(0);

php?>

