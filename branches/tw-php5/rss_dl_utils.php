<?php
		require_once("class.bdecode.php"); 
		require_once("lastRSS.php");
		require_once("atomparser.php");
		require_once("rss_dl.functions.php");
		require_once("tor_client.php");
		require_once("progressbar.php");

		global $config_values;
		$config_values['Global'] = array();
		$config_file = '/share/.torrents/rss_dl.config';
		$time = 0;

    // Checks array is a key is set, return value or default
    function _isset($array, $key, $default = 'Not Specified') {
      return isset($array[$key]) ? $array[$key] : $default;
    }

		function unlink_temp_files() {
			global $config_values;
			if(isset($config_values['Global']['Unlink'])) 
				foreach($config_values['Global']['Unlink'] as $file)
					unlink($file);
		}

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
			// Create the base arrays if not already
			if(!isset($config_values['Favorites']))
				$config_values['Favorites'] = array();
			if(!isset($config_values['Feeds']))
				$config_values['Feeds'] = array();
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
			if(file_exists($config_values['Settings']['History']))
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
			// Dont skip a proper/repack
			if(preg_match('/proper|repack/i', $title))
				return 1;
			$guess = guess_match($title, TRUE);
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
					$cacheguess = guess_match(substr($file, 7), TRUE);
					if($cacheguess != false && $guess['episode'] == $cacheguess['episode']) {
						_debug("Full Episode Match, ignoring\n",2);
						$matched = "duplicate";
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
					$matched = "match";
					if($config_values['Settings']['Verify Episode']) {
						return check_cache_episode($title);
					} else {
						return 1;
					}
				} else {
					$matched = "cachehit";
					return 0;
				}
			} else {
				// No Cache, Always download
				$matched = "match";
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
			* (S\d+E\d+|\d+x\d+|\dof\d|[\d -.]{10})   * (b|c|d|e)Matches Episode Number 
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
			$reg1='/^([^-\(]+)(?:.+)?\b(S\d+E\d+|\d+x\d+|\dof\d|[\d -.]{10})(?:.* (DVB)|.*[\.\(](\w+-\w+)(?:[ \)]\[*\w+\])?|[ \)]+(?:[ \[]+([^\[\]]*)[\]])+|.*\[([\w.]+)\])?.*/';
			if(preg_match($reg1, $title, $regs)) {
				$episode_guess = trim($regs[2]);
				$key_guess = str_replace("'", "&#39;", trim($regs[1]));
				$data_guess = '.*';
				if(isset($regs[3])) { // The last grouping is optional
					for($i = 3;$i < 7;$i++) {
						if($regs[$i] != '') {	
							$data_guess = str_replace("'", "&#39;", trim($regs[$i]));
							break;
						}
					}
				}
			} else
				return False;
			if($normalize == TRUE) {
				// Convert . and _ to spaces, and trim result
				$from = "._";
				$to = "  ";
				$key_guess = trim(strtr($key_guess, $from, $to));
				$data_guess = trim(strtr($data_guess, $from, $to));
				$episode_guess = trim(strtr($episode_guess, $from, $to));
				// Standardize episode output to SSxEE, strip leading 0
				// This is (b|c|d) from earlier.  If it is style e there will be no replacement, only strip leading 0
				$episode_guess = ltrim(preg_replace('/(S(\d+)E(\d+)|(\d+)x(\d+)|(\d)of(\d))/', '\2\4\6x\3\5\7', $episode_guess), '0');
			}
			return array("key" => $key_guess, "data" => $data_guess, "episode" => $episode_guess);
		}

		function setup_rss_list_html() {
			global $html_out, $html_header;
			$html_header = "<div class=feedlist>\n";
			$html_out =  "<div id='torrentlist_container'>\n";
		}
		function finish_rss_list_html() {
			global $html_out, $html_header;
			$html_header .="</div>\n";
			$html_out .=  "</div>\n";
		}
		
		function show_torrent_html($item, $feed, $alt) {
			global $html_out, $matched, $test_run;

			$feed = urlencode($feed);
			$html_out .= "<li class='torrent match_$matched $alt' title='"._isset($item, 'description')."'>";
			$html_out .= "<a class='context_link' href='tw-iface.cgi?mode=matchtitle&rss=$feed&title=".rawurlencode($item['title'])."'></a>";
			$html_out .= "<a class='context_link' href='tw-iface.cgi?mode=dltorrent&title=".rawurlencode($item['title'])."&link=".rawurlencode(get_torrent_link($item))."'></a>";
			$html_out .= "<div class='torrent_name'>".$item['title']."</div>";
			$html_out .= "<div class='torrent_pubDate'>"._isset($item, 'pubDate').'</div>';
			$html_out .= "</li>\n";
		}

	function show_feed_html($rss, $idx) {
		global $html_out;
	
		$html_out .= "<div class='feed' id='feed_$idx'><ul id='torrentlist' class='torrentlist'>";
		$html_out .= "<li class='header'>".$rss['title']."</li>\n";
	}

	function close_feed_html() {
		global $html_out;
		$html_out .= '</ul></div>';
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
			case 'Update':
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
		              "quality"   => "Quality");
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
	
		if(isset($_GET['link']) AND ($tmp = guess_feedtype($_GET['link'])) != 'Unknown') {
			$link = $_GET['link'];
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

	// Return a formatted html link that will call javascript in a normal
	// browser, and in the funky NMT browser
	function _jscript($func, $contents) {
		if($_SERVER["REMOTE_ADDR"] == '127.0.0.1') {
			return('<a href=# onclick="'.$func.';return false;">'.$contents.'</a>');
		} else {
			return('<a href="javascript:'.$func.'">'.$contents.'</a>');
		}
	}
?>
