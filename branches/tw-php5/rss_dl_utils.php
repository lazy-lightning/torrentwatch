<?php
		require_once("class.bdecode.php"); 
		require_once("lastRSS.php");
		require_once("atomparser.php");
		require_once("rss_dl.functions.php");

		global $config_values;
		$config_values['Global'] = array();
		$config_file = '/share/.torrents/rss_dl.config';
		$time = 0;

		// used to ower case all the keys in an array.
		// From http://us.php.net/manual/en/function.array-change-key-case.php
		define('ARRAY_KEY_FC_LOWERCASE', 25); //FOO => fOO
	  define('ARRAY_KEY_FC_UPPERCASE', 20); //foo => Foo
	  define('ARRAY_KEY_UPPERCASE', 15); //foo => FOO
	  define('ARRAY_KEY_LOWERCASE', 10); //FOO => foo
	  define('ARRAY_KEY_USE_MULTIBYTE', true); //use mutlibyte functions
	 
	  /**
	  * change the case of array-keys
	  *
	  * use: array_change_key_case_ext(array('foo' => 1, 'bar' => 2), ARRAY_KEY_UPPERCASE);
	  * result: array('FOO' => 1, 'BAR' => 2)
	  *
	  * @param    array
	  * @param    int
	  * @return     array
	  */
		function array_change_key_case_ext($array, $case = ARRAY_KEY_LOWERCASE) {
			$newArray = array();   
			//for more speed define the runtime created functions in the global namespac
			//get function
			$function = 'strToUpper'; //default
			switch($case) {
				//first-char-to-lowercase
				case 25:
					//maybee lcfirst is not callable
					if(!function_exists('lcfirst'))
						$function = create_function('$input', 'return strToLower($input[0]) . substr($input, 1, (strLen($input) - 1));');
					else 
						$function = 'lcfirst';
					break;
				//first-char-to-uppercase               
				case 20:
					$function = 'ucfirst';
					break;
				//lowercase
				case 10:
					$function = 'strToLower';
			} 
			//loop array
			foreach($array as $key => $value) {
				if(is_array($value)) //$value is an array, handle keys too
					$newArray[$function($key)] = array_change_key_case_ext($value, $case);
				elseif(is_string($key))
					$newArray[$function($key)] = $value;
				else $newArray[$key] = $value; //$key is not a string
			} //end loop
			return $newArray;
		}

		function _debug($string, $lvl = 1) {
			global $config_values, $verbosity, $debug_output;
			if($verbosity >= $lvl) {
				if(isset($config_values['Global']['HTMLOutput']))
					$debug_output .= $string;
				else
					echo($string);
			}
		}

		// This function is from
		// http://www.codewalkers.com/c/a/Miscellaneous/Configuration-File-Processing-with-PHP/2/
		// It has been modified to support multidimensional arrays in the form of
		// group[] = key => data as equivilent of group[key] => data
		function read_config_file() {
			global $config_file;
			global $config_values;
			$comment = ";";
			$group = "NONE";
			$fp = fopen($config_file, "r");

			while (!feof($fp)) {
				$line = trim(fgets($fp));
				if ($line && !ereg("^$comment", $line)) {
					if (ereg("^\[", $line) && ereg("\]$", $line)) {
						$line = trim($line,"[");
						$line = trim($line, "]");
						$group = trim($line);
					} else {
						$pieces = explode("=", $line, 2);
						$pieces[0] = trim($pieces[0] , "\"");
						$pieces[1] = trim($pieces[1] , "\"");
						$option = trim($pieces[0]);
						$value = trim($pieces[1]);
						if(ereg("\[\]$", $option)) {
							$option = substr($option, 0, strlen($option)-2);
							$pieces = explode("=>", $value, 2);
							if(isset($pieces[1])) {
								$config_values[$group][$option][trim($pieces[0])] = trim($pieces[1]);
							} else
								$config_values[$group][$option][] = $value;
						} else
							$config_values[$group][$option] = $value;
					}
				}
			}
			fclose($fp);
		}
	  
		// I wrote the reverse function of the above, please note if you use any
		// command line options those will be written as well
		function write_config_file() {
			global $config_values, $config_file, $config_out;
			_debug("Preparing to write config file to $config_file\n");

			$config_out = ";;\n;; rss_dl.php config file\n;;\n\n";
			function group_callback($group, $key) {
				global $config_values, $config_out;
				if($key == 'Global')
					return;
				$config_out .= "[$key]\n";
				array_walk($config_values[$key], 'key_callback');
				$config_out .= "\n\n";
			}

			function key_callback($group, $key, $subkey = NULL) {
				global $config_values, $config_out;
				if(is_array($group)) {
					array_walk($group, 'key_callback', $key.'[]');
				} else {
					if($subkey) {
						if(!is_numeric($key)) {  // What does this do?
							$group = "$key => $group";
						}
						$key = $subkey;
					}
					$config_out .= "$key = $group\n";
				}
			}
			array_walk($config_values, 'group_callback');
			_debug("Finalized Config\n");
			_debug($config_out,2);
			file_put_contents($config_file, $config_out);
			unset($config_out);
		}
	 
		function add_history($title) { 
			global $config_values;
			$history = unserialize(file_get_contents($config_values['Settings']['History']));
			$history[] = array('Title' => $title, 'Date' => date("m.d.y g:i a"));
			file_put_contents($config_values['Settings']['History'], serialize($history));
		}

		function cache_setup()
		{
			global $config_values, $test_run;
			if($test_run)
				return;
			if(isset($config_values['Settings']['Cache Dir'])) {
				_debug("Enabling Cache\n", 2);
				if(!file_exists($config_values['Settings']['Cache Dir']) ||
				  	!is_dir($config_values['Settings']['Cache Dir'])) {
					if(file_exists($config_values['Settings']['Cache Dir']))
						unlink($config_values['Settings']['Cache Dir']);
					mkdir($config_values['Settings']['Cache Dir'], 777, TRUE);
				}
			}
		}

		function add_cache($title) {
			global $config_values;
			if (isset($config_values['Settings']['Cache Dir'])) {
				$cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($title);
				touch($cache_file);
			}
		}

		/*
		 * Returns 1 if there is no cache hit(dl now)
		 * Returns 0 if there is a hit
		 */
		function check_cache_episode($title) {
			global $config_values, $matched;
			$matched = 2;
			// Dont skip a proper/repack
			if(preg_match('/proper|repack/i', $title))
				return 1;
			$guess = guess_match($title);
			if($guess == False) {
				_debug("Unable to guess for $title\n");
				return 1;
			}
			if($handle = opendir($config_values['Settings']['Cache Dir'])) {
				while(false !== ($file = readdir($handle))) {
					if(!(substr($file, 0,7) == "rss_dl_"))
						continue;
					if(!(substr($file, 7, strlen($guess['key'])) == $guess['key']))
						continue;
					$cacheguess = guess_match(substr($file, 7));
					if($cacheguess != false && $guess['episode'] == $cacheguess['episode']) {
						_debug("Full Episode Match, ignoring\n",2);
						$matched = 3;
						return 0;
					}
				}
			} else {
				_debug("Unable to open ".$config_values['Settings']['Cache Dir']."\n");
			}
			return 1;
		}


		/* Returns 1 if there is no cache hit(dl now)
		 * Returns 0 if there is a hit
		 */
		function check_cache($title)
		{
			global $config_values, $matched;

			if (isset($config_values['Settings']['Cache Dir'])) {
				$cache_file = $config_values['Settings']['Cache Dir'].'/rss_dl_'.filename_encode($title);
				if (!file_exists($cache_file)) {
					if($config_values['Settings']['Verify Episode']) {
						return check_cache_episode($title);
					} else {
						$matched = 2;
						return 1;
					}
				} else {
					$matched = 1;
					return 0;
				}
			} else {
				// No Cache, Always download
				$matched = 2;
				return 1;
			}
		}

		function get_torrent_link($rs) {
			if(isset($rs['id'])) { // Atom
				if(stristr($rs['id'], 'torrent')) // torrent link in id
					return $rs['id'];
				else // torrent hidden in summary
					return guess_atom_torrent($rs['summary']);
			} else if(isset($rs['enclosure'])) { // RSS Enclosure
				return $rs['enclosure']['url'];
			} else {  // Standard RSS
				return $rs['link'];
			}
		}

		function microtime_float() {
			list($usec, $sec) = explode(" ", microtime());
			return ((float)$usec + (float)$sec);
		}
		function timer_init() {
			global $time_start;
			$time_start = microtime_float();
		}
		function timer_get_time() {
			global $time_start;
			return (microtime_float() - $time_start);
		}

		function guess_match($title, $normalize = FALSE) { 
			/* regexp explanation
			* 3 main parts
			* a) /^([^-\(]+)(?:.+)?\b                        * a Matches Name and has an optional match to filter episode title when split with a -
			* (?:S\d+E\d+|\d+x\d+|\dof\d|[\d -.]{10})   * (b|c|d|e)Matches Episode Number 
				b) S\d+E\d+        * S12E1
				c) \d+x\d+         * 1x23
				d) \dof\d          * 3of5
				e) [\d -.]{10}     * 2008-03-23 or 07.23.2008 or .20082306. etc.
			* (?:.* (DVB)|.*[\.\(](\w+-\w+)(?:[ \)]\[*\w+\])?|[ \)]+(?:[ \[]+([^\[\]]*)[\]])+|.*\[([\w.]+)\])?  * (f|g|h|i)? Matches release group/rip type
				f) .* (DVB)                                  * Title ends in " DVB" catches a style with no delimiters
				g) .*[\.\(](\w+-\w+)(?:[ \)]\[*\w+\])?       * Matches Rip-Group at end of title with optional [XxX] ignored afterwards: XviD-XXxxXX
					1) .*[\.\(](\w+-\w+)     * Moves to end and Matches Rip-Group with . or ( directly before
					2)(?:[ \)]\[*\w+\])?     * Optinal [XxX] preceded by ' ' or )
				h) [ \)]+(?:[ \[]+([^\[\]]*)[\]])+           * Matches [rip - group] right after the episode #: [XviD - XXxxXX] preceded by " " or )
				i) .*\[([\w.]+)\]                            * matches a title with a name(possibly with a .) inside a [] at the end as last case: [XXxx.XXX]
			* | means or
			* () groups the or statements
			* ? makes the last grouping optional for a title only match
			* So the full expression is simply a(b|c|d|e)(f|g|h|i)?
			*/
			$reg1='/^([^-\(]+)(?:.+)?\b(S\d+E\d+|\d+x\d+|\dof\d|[\d -.]{10})(?:.* (DVB)|.*[\.\(](\w+-\w+)(?:[ \)]\[*\w+\])?|[ \)]+(?:[ \[]+([^\[\]]*)[\]])+|.*\[([\w.]+)\])?/';
			if(preg_match($reg1, $title, $regs)) {
				$episode_guess = trim($regs[2]);
				$key_guess = str_replace("'", "&#39;", trim($regs[1]));
				$data_guess = '.*';
				for($i = 3;$i < 7;$i++) {
					if(strcmp($regs[$i], '') != 0) {
						$data_guess = str_replace("'", "&#39;", trim($regs[$i]));
						break;
					}
				}
			} else
				return False;
			if($normalize) {
				// Convert . and _ to spaces, and trim result
				$from = "._";
				$to = "  ";
				$key_guess = trim(strtr($key_guess, $from, $to));
				$data_guess = trim(strtr($data_guess, $from, $to));
				$episode_guess = trim(strtr($episode_guess, $from, $to));
			}
			return array("key" => $key_guess, "data" => $data_guess, "episode" => $episode_guess);
		}

		function setup_rss_list_html() {
			global $html_out, $html_header;
			$html_header = "<div class=feedlist>\n";
			$html_out =  "<div class=torrentlist><table>\n";
		}
		function finish_rss_list_html() {
			global $html_out, $html_header;
			$html_header .="</div>\n";
			$html_out .=  "</table></div>\n";
		}
		
		function show_torrent_html($item, $feed, $alt) {
			global $html_out, $matched, $test_run;

			$feed = urlencode($feed);
			$html_out .=  "<tr class='item $alt'>\n<td>";
			$html_out .= "<a href='tw-iface.cgi?mode=matchtitle&rss=$feed&title=".rawurlencode($item['title'])."'>";
			$html_out .=  "<img src='images/rss.png'>".str_replace('.', '.<wbr>', $item['title']);
			$html_out .= "</a>";
			$html_out .=  "</td>\n";
			if(isset($item['id'])) { // ATOM
				$html_out .= "<td>".strip_tags($item['summary'])."</td>\n";
				//$html_out .= "<td>".date("M j h:ia", strtotime($item['published']))."</td>\n";
				$html_out .="<td>".$item['published']."</td>\n";
			} else { // RSS
				$html_out .=  "<td>".str_replace('.', '.<wbr>', $item['description'])."</td>\n";
				if(isset($item['pubDate']))
					$html_out .=  "<td>".$item['pubDate']."</td>\n";
				else
					$html_out .= "<td>Not Specified</td>\n";
			}
			$html_out .= '<td><a href="tw-iface.cgi?mode=dltorrent&link=';
			$html_out .= urlencode(get_torrent_link($item)).'">';
			switch($matched) {
				case 1:
					$html_out .= "<b>Cache Hit</b>";
					break;
				case 2:
					if($test_run)
						$html_out .= "<b>Test Match</b>";
					else
						$html_out .= "<b>Downloaded</b>";
					break;
				case 3:
					$html_out .= "<b>Duplicate</b>";
					break;
				case -1:
					$html_out .= "<b>No Torrent</b>";
					break;
				default:
					$html_out .= "No Match";
					break;
			}	
			$html_out .= "</a></td></tr>\n";
		}

	function show_feed_html($rss) {
		global $html_header, $html_out;
		
		$html_out .= "<tr class='header'><td><br />&nbsp;</td></tr>\n";
		$html_out .= "<tr class='rss'><th class='feedname' colspan='4'><a name='".$rss['title']."'></a>";
		$html_out .= $rss['title']."</td></tr>";
		$html_out .= "<tr class='header'>\n";
		$html_out .= "<th width='200'>Title</th>";
		$html_out .= "<th>Description</th>";
		$html_out .= "<th width='100'>Pub. Date</th>";
		$html_out .= "<th width='80' style='text-align: center;'>Status</th>\n</tr>\n";
		$html_header .= "<div class='feeditem'><a href='#".$rss['title']."'><img src='images/rss.png'>".$rss['title']."</a></div>\n";
	}

	function guess_feedtype($feedurl) {
		global $config_values;
		$content = file($feedurl);
		for($i = 0;$i < count($content);$i++) {
			if(preg_match('/<feed xml/', $content[$i], $regs))
				return 'Atom';
			else if (preg_match('/<rss/', $content[$i], $regs))
				return 'RSS';
		}
		return "Unknown";
	}

	function guess_atom_torrent($summary) {
		$wc = '[\/\:\w\.\+\?\&\=\%\;]+';
		// Detects: A HREF=\"http://someplace/with/torrent/in/the/name\"
		if(preg_match('/A HREF=\\\"(http'.$wc.'torrent'.$wc.')\\\"/', $summary, $regs)) {
			_debug("guess_atom_torrent: $regs[1]\n",2);
			return $regs[1];
		} else {
			_debug("guess_atom_torrent: failed\n",2);
		}
		return FALSE;
	}

	// Makes a name fit for use as a filename
	function filename_encode($filename) {
		return preg_replace("/\?|\/|\\|\+|\=|\>|\<|\,|\"|\*|\|/", "_", $filename);
	}

	// UNUSED
	function transmission_get_settings() {
		return json_decode(file_get_contents('/share/.transmission/settings.json'), TRUE);
	}

	function transmission_rpc($request) {
		$request = json_encode($request);
		$URI = "/transmission/rpc";
		$Host = "localhost";
		$Port = 9091;
		$ReqHeader =
		"POST $URI HTTP/1.1\r\n".
		"Host: $Host\r\n".
		"Connection: Close\r\n".
		"Content-Type: application/json\r\n\r\n".
		"$request\r\n\r\n";
	
		$socket = fsockopen($Host, $Port, $errno, $errstr);
		if (!$socket) {
			return array("errno" => $errno, "errstr" => $errstr);
		}
	
		$idx = 0;
		$skip = 1;
		$raw = "";
		fputs($socket, $ReqHeader);
		while(!feof($socket)) {
			$responce[$idx] = fgets($socket, 128);
			if($skip == 1 && ereg("^{", $responce[$idx]))
				$skip = 0;
			if(!$skip)
				$raw .= $responce[$idx];
			$idx++;
		}
		fclose($socket);
		return json_decode($raw, TRUE);
	}

	function get_deep_dir($tor_name) {
			global $config_values;
			switch($config_values['Settings']['Deep Directories']) {
				case '0':
					break;
				case 'Title':
					$guess = guess_match($tor_name, TRUE);
					if(isset($guess['key'])) {
						$dest = "$dest/".$guess['key'];
						break;
					}
					_debug("Deep Directories: Couldn't match $tor_name Reverting to Full\n", 1);
				case 'Full':
				default:
					$dest = "$dest/".$tor_name;
					break;
			}
			return $dest;
	}

	function btpd_add_torrent($tor, $dest, $autostart) {
		$btcli = '/mnt/syb8634/bin/btcli';
		$btcli_add = 'add -d';
		$btcli_connect='-d /opt/sybhttpd/localhost.drives/HARD_DISK/.btpd/';
		$btcli_exec="$btcli $btcli_connect";

		$tmpname = tempnam("","torrentwatch");
		file_put_contents($tmpname, $tor);
		if($autostart == 0)
			$btcli_add .= " -N";
		exec("$btcli_exec $btcli_add \"$dest\" \"$tmpname\"", $output, $return);
		unlink($tmpname);
		return $return;
	}

	function transmission122_add_torrent($tor, $dest, $autostart) {
		// This should still work for the 13x series, although -g has been reassigned and might confuse 
		$trans_remote = '/mnt/syb8634/bin/transmission-remote';
		$trans_connect = '-g /share/.transmission/';
		$trans_exec = "$trans_remote $trans_connect";
		$trans_add = '-a';

		$tmpname = tempnam("","torrentwatch");
		file_put_contents($tmpname, $tor);
		exec("$trans_exec $trans_add \"$tmpname\"", $output, $return);
		unlink($tmpname);
		return $return;
	}

	function transmission13x_add_torrent($tor, $dest, $autostart) {
		// transmission dies with bad folder if it doesn't end in a /
		if(substr($dest, strlen($dest)-1, 1) != '/')
			$dest .= '/';
		$request = array('method' => 'torrent-add', 'arguments' => array('download-dir' => $dest, 'metainfo' => base64_encode($tor)), 'paused' => $autostart ? 0 : 1);
		$responce = transmission_rpc($request);
		if(isset($responce['result']) AND ($responce['result'] == 'success' or $responce['result'] == 'duplicate torrent'))
			return 0;
		else {
			_debug(print_r($responce));
			return 1;
		}
	}

	function client_add_torrent($filename, $dest, $fav = NULL) {
		global $config_values, $hit;
		$autostart = $config_values['Settings']['AutoStart'];
		if(!$hit && isset($config_values['Global']['HTMLOutput']))
			echo("Starting new torrents<br>");
		$hit = 1;
	
		if(!($tor = file_get_contents($filename))) {
			_debug("Couldn't open torrent: $filename\n",0);
			return FALSE;
		}
		$tor_info = new BDecode("", $tor);
		if(!($tor_name = $tor_info->{'result'}['info']['name'])) {
			_debug("Couldn't parse torrent: $filename\n", 0);
			return FALSE;
		}
		if(isset($fav) && $fav['AutoStart'] != 'Default')
			$autostart = $fav['AutoStart'];
		if(!isset($dest)) {
			$dest = $config_values['Settings']['Download Dir'];
		}
		if(isset($fav) && $fav['Save In'] != 'Default') {
			$dest = $fav['Save In'];
		} else if($config_values['Settings']['Deep Directories']) {
			$dest = get_deep_dir($tor_name);
			_debug("Deep Directorys, change dest to $dest\n", 1);
		}
		if(!file_exists($dest) or !is_dir($dest)) {
			if(file_exists($dest))
				unlink($dest);
			mkdir($dest, 777, TRUE);
		}
		switch($config_values['Settings']['Client']) {
			case 'btpd':
				$return = btpd_add_torrent($tor, $dest);
				break;
			case 'transmission1.3x':
			case 'transmission1.32':
				$return = transmission13x_add_torrent($tor, $dest, $autostart);
				break;
			case 'transmission1.22':
				$return = transmission122_add_torrent($tor, $dest, $autostart);
				// Doesn't support setting dest, so here change dest to transmissons
				$tr_state = new BDecode('/share/.transmission/daemon/state');
				$dest = $tr_state->{'result'}['default-directory'];
				break;
			default:
				_debug("Invalid Torrent Client: ".$config_values['Settings']['Client']."\n",0);
				exit(1);
		}
		if($return == 0) {
			add_history($tor_name);
			_debug("Starting: $tor_name in $dest\n",0);
			if(isset($config_values['Global']['HTMLOutput']))
				echo("Starting: $tor_name in $dest<br>\n");
			if($config_values['Settings']['Save Torrents'])
				file_put_contents("$dest/$tor_name.torrent", $tor);
		} else {
			_debug("Failed Starting: $tor_name  Return code $return\n",0);
			if(isset($config_values['Global']['HTMLOutput']))
				echo("Failed Starting: $tor_name  Return code $return<br>\n");
		}
		return ($return ? 0 : 1);
	}	

	function check_for_torrents($directory, $dest) {
		if($handle = opendir($directory)) {
			while(false !== ($file = readdir($handle))) {
				if(preg_match('/\.torrent$/', $file) && client_add_torrent("$directory/$file", $dest))
						unlink("$directory/$file");
			}
			closedir($handle);
		} else {
			_debug("check_for_torrents: Couldn't read Directory: $directory\n", 0);
			exit(1);
		}
	}
	
	function update_favorite() {
		global $test_run;
		if(!isset($_GET['button']))
			return;
		switch($_GET['button']) {
			case 'Add':
			Case 'Update':
				add_favorite();
				$test_run = TRUE;
				break;
			case 'Delete':
				del_favorite();
				break;
		}
		write_config_file();
	}
	
	function update_feed() {
		if(!isset($_GET['button']))
			return;
		switch($_GET['button']) {
			case 'Add':
				add_feed();
				break;
			case 'Delete':
				del_feed();
				break;
		}
		write_config_file();
	}
	
	function add_favorite() {
		global $config_values;
		$i = 0;
		if(isset($_GET['idx']) && $_GET['idx'] != 'new') {
			$idx = $_GET['idx'];
		} else if(isset($_GET['name']))	{
			$config_values['Favorites'][]['Name'] = $_GET['name'];
			$idx = end(array_keys($config_values['Favorites']));
			$_GET['idx'] = $idx; // So display_favorite_info() can see it
		} else
			return; // Bad form data
		$list = array("name"      => "Name",
									"filter"    => "Filter", 
		              "not"       => "Not",
		              "savein"    => "Save In",
		              "episodes"  => "Episodes",
		              "feed"      => "Feed",
		              "quality"   => "Quality",
		              "autostart" => "AutoStart");
		foreach($list as $key => $data) {
			if(isset($_GET[$key]))
				$config_values['Favorites'][$idx][$data] = urldecode($_GET[$key]);
			else
				$config_values['Favorites'][$idx][$data] = "";
		}
	}
	
	function del_favorite() {
		global $config_values;
		if(isset($_GET['idx']) AND isset($config_values['Favorites'][$_GET['idx']])) {
			unset($config_values['Favorites'][$_GET['idx']]);
		}
	}
	
	
	function add_feed() {
		global $config_values;
		
		if(isset($_GET['link']) AND ($tmp = guess_feedtype(urldecode($_GET['link']))) != 'Unknown') {
			$link = urldecode($_GET['link']);
			$config_values['Feeds'][]['Link'] = $link;
			$idx = end(array_keys($config_values['Feeds']));
			$config_values['Feeds'][$idx]['Type'] = $tmp;
			load_feeds(array(0 => array('Type' => $tmp, 'Link' => $link)));
			switch($tmp) {
				case 'RSS':
					$config_values['Feeds'][$idx]['Name'] = $config_values['Global']['Feeds'][$link]['title'];
					break;
				case 'Atom':
					$config_values['Feeds'][$idx]['Name'] = $config_values['Global']['Feeds'][$link]['Name'];
					break;
			}
		}
	}

	function del_feed() {
		global $config_values;
		if(isset($_GET['idx']) AND isset($config_values['Feeds'][$_GET['idx']])) {
			unset($config_values['Feeds'][$_GET['idx']]);
		}
	}
?>
