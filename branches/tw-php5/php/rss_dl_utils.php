<?php
		require_once("atomparser.php");
		require_once("cache.php");
		require_once("class.bdecode.php"); 
		require_once("config.php");
		require_once("feeds.php");
		require_once("html.php");
		require_once("lastRSS.php");
		require_once("progressbar.php");
		require_once("tor_client.php");

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
		* @param		array
		* @param		int
		* @return		 array
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

		function add_history($title) { 
			global $config_values;
			if(file_exists($config_values['Settings']['History']))
				$history = unserialize(file_get_contents($config_values['Settings']['History']));
			$history[] = array('Title' => $title, 'Date' => date("m.d.y g:i a"));
			file_put_contents($config_values['Settings']['History'], serialize($history));
		}

		function get_torrent_link($rs) {
			if(isset($rs['id'])) { // Atom
				if(stristr($rs['id'], 'torrent')) // torrent link in id
					return $rs['id'];
				else // torrent hidden in summary
					return guess_atom_torrent($rs['summary']);
			} else if(isset($rs['enclosure'])) { // RSS Enclosure
				return $rs['enclosure']['url'];
			} else {	// Standard RSS
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
			* a) /^([^-\(]+)(?:.+)?\b												* a Matches Name and has an optional match to filter episode title when split with a -
			* (S\d+E\d+|\d+x\d+|\d+of\d+|[\d -.]{10})	 * (b|c|d|e)Matches Episode Number 
				b) S\d+E\d+				* S12E1
				c) \d+x\d+				 * 1x23
				d) \d+of\d+					* 3of5
				e) [\d -.]{10}		 * 2008-03-23 or 07.23.2008 or .20082306. etc.
			* (?:.* (DVB)|.*[\.\(](\w+-\w+)(?:[ \)]\[*\w+\])?|[ \)]+(?:[ \[]+([^\[\]]*)[\]])+|.*\[([\w.]+)\])?	* (f|g|h|i)? Matches release group/rip type
				f) .* (DVB)																	* Title ends in " DVB" catches a style with no delimiters
				g) .*[\.\(](\w+-\w+)(?:[ \)]\[*\w+\])?			 * Matches Rip-Group at end of title with optional [XxX] ignored afterwards: XviD-XXxxXX
					1) .*[\.\(](\w+-\w+)		 * Moves to end and Matches Rip-Group with . or ( directly before
					2)(?:[ \)]\[*\w+\])?		 * Optinal [XxX] preceded by ' ' or )
				h) [ \)]+(?:[ \[]+([^\[\]]*)[\]])+					 * Matches [rip - group] right after the episode #: [XviD - XXxxXX] preceded by " " or )
				i) .*\[([\w.]+)\]														* matches a title with a name(possibly with a .) inside a [] at the end as last case: [XXxx.XXX]
			* | means or
			* () groups the or statements
			* ? makes the last grouping optional for a title only match
			* So the full expression is simply a(b|c|d|e)(f|g|h|i)?
			*/
			$reg1='/^([^-\(]+)(?:.+)?\b(S\d+E\d+|\d+x\d+|\d+of\d+|[\d -.]{10})(?:.* (DVB)|.*[\.\(](\w+-\w+)(?:[ \)]\[*\w+\])?|[ \)]+(?:[ \[]+([^\[\]]*)[\]])+|.*\[([\w.]+)\])?.*/';
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
				$to = "	";
				$key_guess = trim(strtr($key_guess, $from, $to));
				$data_guess = trim(strtr($data_guess, $from, $to));
				$episode_guess = trim(strtr($episode_guess, $from, $to));
				// Standardize episode output to SSxEE, strip leading 0
				// This is (b|c|d) from earlier.	If it is style e there will be no replacement, only strip leading 0
				$episode_guess = ltrim(preg_replace('/(S(\d+)E(\d+)|(\d+)x(\d+)|(\d+)of(\d+))/', '\2\4\6x\3\5\7', $episode_guess), '0');
			}
			return array("key" => $key_guess, "data" => $data_guess, "episode" => $episode_guess);
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
	
?>