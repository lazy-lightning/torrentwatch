<?php
    
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

		// backwards compatible function for PHP4
		// Function from http://us.php.net/manual/en/function.file-put-contents.php#68329
		if ( !function_exists('file_put_contents') ) {
			if(!defined('FILE_APPEND') )
				define('FILE_APPEND', 1);
			function file_put_contents($n, $d, $flag = false) {
				$mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'w';
				$f = @fopen($n, $mode);
				if ($f === false) {
					return 0;
				} else {
					if (is_array($d)) $d = implode($d);
					$bytes_written = fwrite($f, $d);
					fclose($f);
					return $bytes_written;
				}
			}
		}
  
		function _debug($string, $lvl = 1) {
			global $config_values, $verbosity, $html_footer;
			if($verbosity >= $lvl) {
				if(isset($config_values['Global']['HTMLOutput']))
					$html_footer .= $string;
				else
					echo($string);
      }
    }

		function fetch_http($url, $destfile) {
			global $config_values;  
			_debug("fetch_http(): url: $url dest: $destfile\n",3);
			/* Some rss feeds give bad urls, fix that here */
			$url = str_replace('&amp;', '&', $url);
			if ($config_values['Settings']['Use wget']) {
				exec('wget -q -O - '.escapeshellarg($url).' > '.escapeshellarg($destfile));
			} else {
				_debug("Bad Config.  Only wget is currently supported\n\n",-1);
				exit(1);
			}
		}

		// This function is from
		// http://www.codewalkers.com/c/a/Miscellaneous/Configuration-File-Processing-with-PHP/2/
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
						$pieces = explode("=", $line);
						$pieces[0] = trim($pieces[0] , "\"");
						$pieces[1] = trim($pieces[1] , "\"");
						$option = trim($pieces[0]);
						$value = trim($pieces[1]);
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

			function key_callback($group, $key) {
				global $config_values, $config_out;
				$config_out .= "$key = $group\n";
			}
			array_walk($config_values, 'group_callback');
			_debug("Finalized Config\n");
			_debug($config_out,2);
			file_put_contents($config_file, $config_out);
			unset($config_out);
		}
    
		if(!defined('RSS_ADD'))
			define('RSS_ADD', 1);
		if(!defined('RSS_DEL'))
			define('RSS_DEL', 2);
		function update_config($type, $argc, $argv, $pos) {
			global $config_values;
			if($pos+4 != $argc) {
				_debug("Wrong number of arguments for update_config()\n",0);
				//_debug("type: $type argc:$argc pos:$pos argv:".implode(" ",$argv)."\n");
				exit(1);
			}
			$rss = $argv[$pos+1];
			$key = $argv[$pos+2];
			$data = $argv[$pos+3];
			switch($type) {
				case RSS_ADD:
					if(isset($config_values[$rss][$key])) {
						_debug("$rss already has a match for $key.  Try a different key value.\n");
						exit(1);
					}
					$config_values[$rss][$key] = $data;
					_debug("Match for $key $data added to $rss\n");
					break;
				case RSS_DEL:
					if(isset($config_values[$rss]) && strcmp($config_values[$rss][$key], $data) == 0) {
						unset($config_values[$rss][$key]);
						_debug("Match for $key = $data removed from $rss\n");
						if(count($config_values[$rss]) == 0) {
							unset($config_values[$rss]);
							_debug("$rss has no more Matches, Removing.\n",2);
						}
					} else {
						_debug("No matching key/data pair to remove\n");
						_debug("feed: $rss key: $key data: $data\n");
						if(!isset($config_values[$rss]))
							_debug("No rss set\n");
						else if(!isset($config_values[$rss][$key]))
							_debug("Key not set\n");
						else if(strcmp($config_values[$rss][$key], $data) != 0)
							_debug("Data doesn't match\ndata: \"".$config_values[$rss][$key]."\" != \"$data\"\n");
						exit(1);
					}
					break;
				default:
					_debug("Bad Type sent to update_config()\n", 0);
					exit(1);
			}
			write_config_file();
			exit(0);
		}

		function cache_setup()
		{
			global $config_values, $test_run;
			if($test_run)
				return;
			if(isset($config_values['Settings']['Cache Dir'])) {
				_debug("Enabling Cache\n", 2);
				// mkdir from php4.0 is kind sucky.  need 5.0 for the recursion
				//mkdir($config_values['Settings']['Cache Dir'], 777, TRUE);
				exec('mkdir -p '.$config_values['Settings']['Cache Dir']);
				chmod($config_values['Settings']['Cache Dir'], 777);
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
			$guess = guess_match($title);
			if($guess == False) {
				_debug("Unable to guess for $title\n");
				return 1;
			}
			if($handle = opendir($config_values['Settings']['Cache Dir'])) {
				while(false !== ($file = readdir($handle))) {
					if(!(substr($file, 0,7) == "rss_dl_")) {
						continue;
					}
					if(!(substr($file, 7, strlen($guess['key'])) == $guess['key'])) {
						continue;
					}
					preg_match('/rss_dl_(.*)/', $file, $matches);
					$cacheguess = guess_match($matches[1]);
					if($guess['episode'] == $cacheguess['episode']) {
						_debug("Full Episode Match, ignoring\n",2);
						// touch($config_values['Settings']['Cache Dir']."/rss_dl_".$title);
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
			global $config_values;

			if (isset($config_values['Settings']['Cache Dir'])) {
				$cache_file = $config_values['Settings']['Cache Dir'] . '/rss_dl_' . filename_encode($title);
				if (!file_exists($cache_file)) {
					if($config_values['Settings']['Verify Episode'])
						return check_cache_episode($title);
					else
						return 1;
				} else {
					return 0;
				}
			} else {
				// No Cache, Always download
				return 1;
			}
		}

		function get_torrent_link($rs) {
			if(isset($rs['id'])) { // Atom
				if(stristr($rs['id'], 'torrent')) // torrent link in id
					return $rs['id'];
				else {// torrent hidden in summary
					$url = guess_atom_torrent($rs['summary']);
					if($url)
						return $url;
					else {
						return NULL;
					}
				}
			} else if(isset($rs['enclosure'])) { // RSS Enclosure
				return $rs['enclosure']['url'];
			} else {  // Standard RSS
				return $rs['link'];
			}
		}

		function fetch_torrent($title, $link) {
			global $test_run, $matched, $config_values;
			$matched = 1;
			$title = filename_encode($title);
			$destdir = $config_values['Settings']['Watch Dir'];
			if(check_cache($title)) {
				$matched = 2;
				if($test_run) {
					_debug("Test Run, ignoring.\n");
					return;
				}
				add_cache($title);
				_debug("Downloading. ");
				_debug("$link\n");
				fetch_http($link, $destdir.'/'.$title.'.torrent');
			} else {
				if($matched == 3)
					_debug("Duplicate Episode, ignoring.\n");
				else
					_debug("Cache hit, ignoring.\n");
			}
		}

		function update_btcli()
		{
			global $config_values;
			_debug("Running BTCLI Update Program\n");
			exec('/share/.torrents/torrentwatch.php check', $output);
			if(isset($config_values['Global']['HTMLOutput']))
				btcli_html($output);
			_debug(implode("\n", $output)."\n",0);

		}

    // http://us2.php.net/manual/en/function.urldecode.php#34280
		// not sure why i used this function, for now just use normal urlencode
		// this function is *super* slow.  dropped 2.5s of execution time by losing it
		function my_urlencode($string){
			return urlencode($string);

			$start = array(":", "/", "?", "=", "&");
			$finish = array("%3A", "%2F", "%3F",  "%3D", "%26");
			$finalstring = "";
    
			for($i=0;$i < strlen($string); $i++) {
				$matched = 0;
				for($j = 0;$j < count($start); $j++) {
					if(strcmp(substr($string, $i, 1), $start[$j]) == 0) {
						$matched = 1;
						$finalstring .= $finish[$j];
					}
				}
				if($matched == 0) {
					$finalstring .= substr($string, $i, 1);
				}
			}

			return $finalstring;
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

		function guess_match($title) { 
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
			return array("key" => $key_guess, "data" => $data_guess, "episode" => $episode_guess);
		}

		function setup_rss_list_html() {
			global $html_out, $html_header, $html_footer;
			$html_header = "<div class=feedlist>\n";
			$html_footer = "<div class=rss_debug><p>\n";
			$html_out =  "<div class=torrentlist><table>\n";
		}
		function finish_rss_list_html() {
			global $html_out, $html_header, $html_footer;
			$html_header .="</div>\n";
			$html_out .=  "</table></div>\n";
		}
		function finish_html() {
			global $html_header,$html_out,$html_footer;
			$html_footer .= "</p></div>\n";
			echo $html_header.$html_out.$html_footer;
		}
		
		function show_torrent_html($item, $feed, $alt) {
			global $html_out, $matched, $test_run;

			$feed = my_urlencode($feed);
			$html_out .=  "<tr class='item $alt'>\n<td class='title'>";
			$html_out .= "<a href='tw-iface.cgi?mode=matchtitle&rss=$feed&title=".rawurlencode($item['title'])."'>";
			$html_out .= str_replace('.', '.<wbr>', $item['title']);
			$html_out .= "</a>";
			$html_out .=  "</td>\n";
			if(isset($item['id'])) { // ATOM
				$html_out .= "<td>".strip_tags($item['summary'])."</td>\n";
				//$html_out .= "<td>".date("M j h:ia", strtotime($item['published']))."</td>\n";
				$html_out .="<td>".$item['published']."</td>\n";
			} else { // RSS
				$html_out .=  "<td>".str_replace('.', '.<wbr>', $item['description'])."</td>\n";
				$html_out .=  "<td>".date("M j h:ia", strtotime($item['pubDate']))."</td>\n";
			}
			switch($matched) {
				case 1:
					$html_out .= "<td><b>Cache Hit</b></td>";
					break;
				case 2:
					if($test_run)
						$html_out .= "<td><b>Test Match</b></td>";
					else
						$html_out .= "<td><b>Downloaded</b></td>";
					break;
				case 3:
					$html_out .= "<td><b>Duplicate</b></td>";
					break;
				case -1:
					$html_out .= "<td><b>No Torrent</b></td>";
					break;
				default:
					$html_out .= '<td><a href="tw-iface.cgi?mode=dltorrent&title='.urlencode($item['title']).'&link=';
					$html_out .= urlencode(get_torrent_link($item)).'">No Match</a></td>';
					break;
			}	
			$html_out .= "</tr>\n";
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
	$html_header .= "<div class='feeditem'><a href='#".$rss['title']."'>".$rss['title']."</a></div>\n";
}
function btcli_html($output) {
	global $html_header;
	$tmp = "<div class='btcli'>\n";
	$tmp .= implode('<br />', $output);
	$tmp .= "</div>\n";
	$html_header = $tmp.$html_header;
}

define('FEED_UNKNOWN', 0);
define('FEED_ATOM', 1);
define('FEED_RSS', 2);
function guess_feedtype($feedfile) {
	if(!file_exists($feedfile))
		return FEED_UNKNOWN;

	$content = file($feedfile);
	for($i = 0;$i < count($content);$i++) {
		if(preg_match('/<feed xml/', $content[$i], $regs))
			return FEED_ATOM;
		else if (preg_match('/<rss/', $content[$i], $regs))
			return FEED_RSS;
	}
	return FEED_UNKNOWN;
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

function transmission_get_dl_dir() {
	$capture = 0;
	$contents = file_get_contents("/share/.transmission/daemon/state");
	$opts = explode(":", $contents);
	foreach($opts as $opt) {
		$opt = trim($opt);
		$len = strlen($opt);
		for($i = strlen($opt)-1; preg_match("/[0-9]/", $opt[$i]); $i--)
			;
		$string = substr($opt, 0, $i+1);
		if($capture == 1) {
			return $string;
		}
		if($string == "default-directory")
			$capture = 1;
	}
	return Null;
}
?>