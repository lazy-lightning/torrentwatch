#!/mnt/syb8634/server/php
<?php
		ini_set('include_path', '.:/share/.torrents');
		ini_set("precision", 4);
    
		// This is our RSS parser.
		// Found at http://lastrss.oslab.net/
		require_once('lastRSS.php');
		// This is our Atom parser
		// Found at http://www.the-art-of-web.com/php/atom/
		require_once('atomparser.php');

		// These are our extra functions
		require_once('rss_dl_utils.php');

		// **unused** doesn't work right.  We use wget instead
		// This is our HTTP requestor.  It has been hacked to not use the system gethostbyname()
		// and use our own.  It is from
		// http://dev.textcube.org/browser/branches/1.7/lib/components/Eolin.PHP.HTTPRequest.php?rev=6298
		require_once('Eolin.PHP.HTTPRequest.php');

    
		// DEFAULT CONFIG -
		$config_values;
		$config_file = '/share/.torrents/rss_dl.config';
		$test_run = 0;
		$verbosity = 0;

		function usage() {
			global $argv;
			_debug( "$argv[0] <options> - Download torrents from RSS feeds\n",0);
			_debug( "           -a <rssurl> <match1> <match2> : Add new torrent match\n",0);
			_debug( "           -c <dir> : Enable Cache\n",0);
			_debug( "           -C : Disable Cache\n",0);
			_debug( "           -d : Dont run torrentwatch.php (fetch .torrent only)\n",0);
			_debug( "           -D : Run torrentwatch.php (fetch .torrent and begin DL)\n",0);
			_debug( "           -f <file> : cron file to hook\n",0);
			_debug( "           -h : show this help\n",0);
			_debug( "           -H : Output HTML\n",0);
			_debug( "           -i : install cron hook and setup default config\n",0);
			_debug( "           -nv: not verbose (default)\n",0);
			_debug( "           -q : quiet (no output)\n",0);
			_debug( "           -r <rssurl> <match1> <match2> : Remove torrent match\n",0);
			_debug( "           -u : uninstall cron hook\n",0);
			_debug( "           -v : verbose output\n",0);
			_debug( "           -vv: verbose output(even more)\n",0);
			_debug( " Note: When using -a or -r it must be the last option\n\n",0);
		}

		function parse_args() {
			global $config_values, $argc, $argv, $test_run, $verbosity;
			for($i=1;$i<$argc;$i++) {
				switch($argv[$i]) {
					case '-a':
						update_config(RSS_ADD, $argc, $argv, $i);
						break;
					case '-c':
						$i++;
						$config_values['Settings']['Cache Dir'] = $argv[$i];
						break;
					case '-C':
						unset($config_values['Settings']['Cache Dir']);
						break;
					case '-d':
						$config_values['Settings']['Run Torrentwatch'] = 0;
						break;
					case '-D':
						$config_values['Settings']['Run Torrentwatch'] = 1;
						break;
					case '-f':
						$i++;
						$config_values['Settings']['Cron'] = $argv[$i];
						break;
					case '-h':
						usage();
						exit(1);
					case '-H':
						$config_values['Settings']['HTMLOutput'] = 1;
						break;
					case '-i':
						$config_values['Settings']['Install'] = 1;
						break;
					case '-nv':
						$verbosity = 0;
						break;
					case '-q':
						$verbosity = -1;
						break;
					case '-r':
						update_config(RSS_DEL, $argc, $argv, $i);
						break;
					case '-t':
						$test_run = 1;
						break;
					case '-u':
						$config_values['Settings']['Install'] = 2;
						break;
					case '-v':
						$verbosity = 1;
						break;
					case '-vv':
						$verbosity = 2;
						break;
					default:
						_debug("Unknown command line argument: $argv[$i]\n",0);
						break;
				}
			}
		}

		function setup_default_config() {
			global $config_values;
			function _default($a, $b) {
				global $config_values;
				if(!isset($config_values['Settings'][$a])) {
					$config_values['Settings'][$a] = $b;
				}
			}
			read_config_file();
			// Special case for renamed var in 0.6-6
			if(isset($config_values['Settings']['Torrent Dir']))  {
				_default('Watch Dir', $config_values['Settings']['Torrent Dir']);
				unset($config_values['Settings']['Torrent Dir']);
			}
			// Sensible Defaults 
			_default('Watch Dir', "/share/.torrents");
			_default('Download Dir', "/share/Video");
			_default('Use wget', "1");
			_default('Run Torrentwatch', "1");
			_default('Cron', "/etc/cron.hourly");
			write_config_file();
		}

		function setup_cron_hook() {
			global $config_values;
			_debug("Preparing to modify cron hook ...\n");
			if(!(isset($config_values['Settings']['Cron']) || !file_exists($config_values['Settings']['Cron']))) {
				_debug("No Cron file Selected\n",0);
				exit(1);
			}
			$cron = $config_values['Settings']['Cron'];
			$i = $config_values['Settings']['Install'];
			// Check if we are already in the cron.hourly file
			// $return = 0 : already in the file
			// $return > 0 : not in file
			exec("/bin/cat $cron | /bin/grep -q rss_dl.php", $output, $return);
			switch($i) {
				case 1:
					// install hook
					if($return == 0) {
						_debug("Cron hook already installed in $cron\n");
					} else {
						file_put_contents($cron, "/share/.torrents/rss_dl.php -D >> /var/rss_dl.log\n", FILE_APPEND);
						_debug("Cron hook installed to $cron\n",0);
					}
					break;
				case 2:
					//uninstall hook
					if($return > 0) {
						_debug("Cron hook not installed in $cron\n");
					} else {
						exec('grep -v rss_dl.php '.$cron.' > /tmp/rss_dl.tmp');
						copy('/tmp/rss_dl.tmp', $cron);
						_debug("Cron hook removed from $cron\n",0);
					}
					break;
				default:
					_debug("Unknown option $i passed to setup_cron_hook()\n",0);
					exit(1);
			}
			exit(0);
		}

		function feed_callback($group, $key) {
			global $config_values;
			if(strcasecmp($key, "Settings") == 0)
				return;
			_debug("\t\t$key\n",0);
			if($config_values['Settings']['Cache Dir']) {
				$rssfile = $config_values['Settings']['Cache Dir'].'/'.filename_encode($key);
				_debug($key . " becomes " . $rssfile . "\n",2);
				if(!file_exists($rssfile) or time()-filemtime($rssfile) > (60*50)) {  // Cache rss's for 60*50 secconds(50min)
					fetch_http($key, $rssfile);
				}
			} else {
				$rssfile = '/tmp/torrenttest.rss';
				fetch_http($key, $rssfile);
			}
			switch(guess_feedtype($rssfile)) {
				case FEED_RSS:
					parse_one_rss($key, $rssfile);
					break;
				case FEED_ATOM:
					parse_one_atom($key, $rssfile);
					break;
				case FEED_UNKNOWN:
				default:
					_debug("Unknown Feed $key stored in $rssfile\n");
					break;
			}
		}

		function check_for_torrent($item, $key, $rs) {
			global $matched;
			if(preg_match('/'.strtolower($item).'/', strtolower($rs[title])) && preg_match('/'.strtolower($key).'/', strtolower($rs[title]))) {
				_debug('Match found for '.$rs[title]."\t");
				if($link = get_torrent_link($rs)) 
					fetch_torrent($rs[title], $link);
				else {
					_debug("Unable to find URL for ".$rs['title']."\n");
					$matched = -1;
				}
			}
		}

		function parse_one_rss($key, $rssfile) {
			global $config_values, $matched;
			$rss = new lastRSS;
			$rss->stripHTML = True;
			if($config_values['Settings']['Cache Dir'])
				$rss->cache_dir = $config_values['Settings']['Cache Dir'];
			if($rs = $rss->get($rssfile)) {
				if($config_values['Settings']['HTMLOutput'])
					show_feed_html($rs);
				$alt = 'alt';
				foreach($rs['items'] as $item) {
					$matched = 0;
					array_walk($config_values[$key], 'check_for_torrent', $item);
					if($matched == 0) {
						_Debug("No match for $item[title]\n", 2);
					}
					if($config_values['Settings']['HTMLOutput']) {
						show_torrent_html($item, $key, $alt);
					}
					
					if($alt=='alt') {
						$alt='';
					} else {
						$alt='alt';
					}
				}
				unset($item);
      } else {
				_debug("Failed to open rss feed: $key stored in $rssfile\n",0);
			}
		}
    
		function parse_one_atom($key, $atomfile) {
			global $config_values, $matched;


			if($config_values['Settings']['Cache Dir'])
				$atom_parser = new myAtomParser($atomfile, $config_values['Settings']['Cache Dir']);
			else
				$atom_parser = new myAtomParser($atomfile);

			if($atom = $atom_parser->getRawOutput()) {
				$atom = array_change_key_case_ext($atom, ARRAY_KEY_LOWERCASE);
				if($config_values['Settings']['HTMLOutput'])
					show_feed_html($atom['feed']);
				$alt='alt';
				
				foreach($atom['feed']['entry'] as $item) {
					$matched = 0;
					array_walk($config_values[$key], 'check_for_torrent', $item);
					if($matched == 0) {
						_debug("No match for ".$item['title']."\n");
					}
					if($config_values['Settings']['HTMLOutput']) {
						show_torrent_html($item, $key, $alt);
					}
	
					if($alt=='alt') {
						$alt='';
					} else {
						$alt='alt';
					}
					unset($item);
				}
			} else {
				_debug("Failed to parse atom feed: $atomfile \n");
			}
		}
//
//
// Begin Main Function
//
//

	timer_init();
  read_config_file();
	if(isset($config_values['Settings']['Verbose']))
		$verbosity = $config_values['Settings']['Verbose'];
  parse_args();
  _debug(date("F j, Y, g:i a")."\n",0);
  cache_setup();

  // Hooks for auto-run from the cron.hourly script
  if($config_values['Settings']['Install']) {
    if($config_values['Settings']['Install'] == 1)
      setup_default_config();
    setup_cron_hook($config_values['Settings']['Install'], $config_values['Settings']['Cron']);
    exit;
  }

  if($config_values['Settings']['HTMLOutput'])
		setup_rss_list_html();

  _debug("Fetching Feeds ...\n");
  array_walk($config_values, 'feed_callback');


	if($config_values['Settings']['HTMLOutput'])
		finish_rss_list_html();

  if($config_values['Settings']['Run Torrentwatch'] and !$test_run) {
		update_btcli();
  } else {
    _debug("Skipping BTCLI Update\n");
  }

	_debug(timer_get_time()."s\n",0);

	if($config_values['Settings']['HTMLOutput']) {
		finish_html();
	}

?>
