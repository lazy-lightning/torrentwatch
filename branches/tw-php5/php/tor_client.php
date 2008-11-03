<?php
	/*
	 * client.php
	 * Client specific functions
	 */

	// UNUSED
	function transmission_get_settings() {
		return json_decode(file_get_contents('/share/.transmission/settings.json'), TRUE);
	}

	function transmission_rpc($request) {
		$request = json_encode($request);
		$reqLen = strlen("$request\r\n\r\n");
		$URI = "/transmission/rpc";
		$Host = "localhost";
		$Port = 9091;
		$ReqHeader =
		"POST $URI HTTP/1.1\r\n".
		"Host: $Host\r\n".
		"Connection: Close\r\n".
		"Content-Length: $reqLen\r\n".
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

	function btpd_add_torrent($tor, $dest) {
		global $config_values;
		$btcli = '/mnt/syb8634/bin/btcli';
		$btcli_add = 'add -d';
		$btcli_connect='-d /opt/sybhttpd/localhost.drives/HARD_DISK/.btpd/';
		$btcli_exec="$btcli $btcli_connect";

		$tmpname = tempnam("","torrentwatch");
		$config_values['Global']['Unlink'][] = $tmpname;
		file_put_contents($tmpname, $tor);
		exec("$btcli_exec $btcli_add \"$dest\" \"$tmpname\"", $output, $return);
		return $return;
	}

	function transmission122_add_torrent($tor, $dest) {
		// This should still work for the 13x series, although -g has been reassigned and might confuse
		$trans_remote = '/mnt/syb8634/bin/transmission-remote';
		$trans_connect = '-g /share/.transmission/';
		$trans_exec = "$trans_remote $trans_connect";
		$trans_add = '-a';

		$tmpname = tempnam("","torrentwatch");
		$config_values['Global']['Unlink'][] = $tmpname;
		file_put_contents($tmpname, $tor);
		exec("$trans_exec $trans_add \"$tmpname\"", $output, $return);
		return $return;
	}

	function transmission13x_add_torrent($tor, $dest, $seedRatio = -1) {
		// transmission dies with bad folder if it doesn't end in a /
		if(substr($dest, strlen($dest)-1, 1) != '/')
			$dest .= '/';
		$request = array('method' => 'torrent-add', 'arguments' => array('download-dir' => $dest, 'metainfo' => base64_encode($tor)));
		if($seedRatio != "" & $seedRatio >= 0)
			$request['arguments']['ratio-limit'] = $seedRatio;
		$responce = transmission_rpc($request);
		if(isset($responce['result']) AND ($responce['result'] == 'success' or $responce['result'] == 'duplicate torrent'))
			return 0;
		else {
			_debug(print_r($responce));
			return 1;
		}
	}

	function client_add_torrent($filename, $dest, $fav = NULL, $feed = NULL) {
		global $config_values, $hit;
		$hit = 1;

		$stream_opts = array('http' =>array('method' => 'GET'));
		// Support for private trackers using cookies
		if($feed != NULL && ($cookies = stristr($feed, '&:COOKIE:'))) {
			$cookies = substr($cookies, 9);
			$cookies = explode('&', $cookies);
			$stream_opts['http']['header'] = "Cookie: ";
			foreach($cookies as $cookie) {
				$stream_opts['http']['header'] .= " $cookie;";
			}
		}
		$context = stream_context_create($stream_opts);
		if(!($tor = file_get_contents($filename, FALSE, $context))) {
			_debug("Couldn't open torrent: $filename\n",0);
			return FALSE;
		}
		$tor_info = new BDecode("", $tor);
		if(!($tor_name = $tor_info->{'result'}['info']['name'])) {
			_debug("Couldn't parse torrent: $filename\n", 0);
			return FALSE;
		}
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
				$return = transmission13x_add_torrent($tor, $dest, _isset($fav, 'seedRatio', -1));
				break;
			case 'transmission1.22':
				$return = transmission122_add_torrent($tor, $dest);
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
			_debug("Failed Starting: $tor_name	Return code $return\n",0);
		}
		return ($return ? 0 : 1);
	}
?>
