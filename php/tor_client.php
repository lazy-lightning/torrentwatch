<?php
/*
 * client.php
 * Client specific functions
 */


function sabnzbd_addurl($url) {
  $Host = "192.168.1.121";
  $Port = "8080";
  $URI = "sabnzbd/api";

  $request="http://$Host:$Port/$URI?mode=addurl&name=".urlencode($url);

  if($contents = file_get_contents($request)) {
    return 0;
  } else {
    return "Failed to connect with SabNZBd at $Host:$Port/$URI";
  }
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

function get_deep_dir($dest, $tor_name) {
    global $config_values;
    switch($config_values['Settings']['Deep Directories']) {
      case '0':
        break;
      case 'Title':
        $guess = guess_match($tor_name, TRUE);
        if(isset($guess['key'])) {
          $dest = $dest."/".$guess['key'];
          break;
        }
        _debug("Deep Directories: Couldn't match $tor_name Reverting to Full\n", 1);
      case 'Full':
      default:
        $dest = $dest."/".$tor_name;
        break;
    }
    return $dest;
}

function folder_add_torrent($tor, $dest, $title) {
  global $config_values;
  // remove invalid chars
  $title = strtr($title, '/', '_');
  // add the directory and extension
  $dest = "$dest/$title.".$config_values['Settings']['Extension'];
  // save it
  file_put_contents($dest, $tor);
  return 0;
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
  return $return == 0 ? 0 : "$btcli exited with return status of $return";
}

function transmission122_add_torrent($tor, $dest) {
  // This should still work for the 13x series, although -g has been reassigned and might confuse
  $trans_remote = platform_getTransmissionRemote();
  $trans_connect = '-g /share/.transmission/';
  $trans_exec = "$trans_remote $trans_connect";
  $trans_add = '-a';

  $tmpname = tempnam("","torrentwatch");
  $config_values['Global']['Unlink'][] = $tmpname;
  file_put_contents($tmpname, $tor);
  exec("$trans_exec $trans_add \"$tmpname\"", $output, $return);
  return $return == 0 ? 0 : "$trans_remote exited with return status of $return";
}

function transmission13x_add_torrent($tor, $dest, $seedRatio = -1) {
  // transmission dies with bad folder if it doesn't end in a /
  if(substr($dest, strlen($dest)-1, 1) != '/')
    $dest .= '/';
  $request = array('method' => 'torrent-add', 
                   'arguments' => array('download-dir' => $dest, 
                                        'metainfo' => base64_encode($tor)
                                       )
                               );
  if($seedRatio != "" && $seedRatio >= 0)
    $request['arguments']['ratio-limit'] = $seedRatio;
  $responce = transmission_rpc($request);
  if(isset($responce['result']) AND ($responce['result'] == 'success' or $responce['result'] == 'duplicate torrent'))
    return 0;
  else {
    if(!isset($responce['result']))
      return "Failure connecting to Transmission >= 1.30";
    else
      return "Transmission RPC Error: ".print_r($responce, TRUE);
  }
}

function nzbget_add_nzb($filename, $title) {
  global $config_values;
  $be = new BrowserEmulator();
  if(!($nzb = $be->file_get_contents($filename))) {
    _debug("Couldn't open nzb: $filename\n",-1);
    return FALSE;
  }
  $nzb_exec = "/mnt/syb8634/bin/nzbget";
  $nzb_connect = "-c /share/.nzbget/nzbget.conf";

  $tmpname = tempnam("","torrentwatch-$title-");
  $config_values['Global']['Unlink'][] = $tmpname;
  file_put_contents($tmpname, $nzb);
  $nzb_append = "-A '$tmpname'";

  exec("$nzb_exec $nzb_connect $nzb_append", $output, $return);
  return $return == 0 ? 0 : "$nzb_exec exited with return code $return";
}


function client_add_nzb($filename, $title = NULL, &$fav = NULL, $feed = NULL) {
  global $config_values, $hit;
  $hit = 1;
  $filename = htmlspecialchars_decode($filename);

  if(empty($title)) {
    if(isset($fav))
      $title = $fav['Name'];
    else
      $title = $filename;
  }

  switch($config_values['Settings']['Client']) {
    case 'sabnzbd':
      $return = sabnzbd_addurl($filename);
      break;
    case 'nzbget':
      $return = nzbget_add_nzb($filename, $title);
      break;
  }
  if($return === 0) {
    add_history($title);
    if(!empty($fav))
      updateFavoriteEpisode($fav, $title);
    _debug("Started: $title\n",0);
  } else {
    _debug("Failed Starting $title. Error: $return",-1);
  }
  return ($return === 0);
}
  
function client_add_torrent($filename, $dest, $title, &$fav = NULL, $feed = NULL) {
  global $config_values, $hit;
  $hit = 1;
  $filename = htmlspecialchars_decode($filename);

  // Detect and append cookies from the feed url
  $url = $filename;
  if($feed && $cookies = stristr($feed, ':COOKIE:')) {
    $url .= $cookies;
  }

  $be = new BrowserEmulator();
  $be->addHeaderLine("User-Agent", 'Python-urllib/1.17');
  if(!($tor = $be->file_get_contents($url))) {
  print '<pre>'.print_r($_GET, TRUE).'</pre>';
    _debug("Couldn't open torrent: $filename\n",-1);
    return FALSE;
  }
  $tor_info = new BDecode("", $tor);
  if(!($tor_name = $tor_info->{'result'}['info']['name'])) {
    $tor_name = $title;
  }

  if(!isset($dest)) {
    $dest = $config_values['Settings']['Download Dir'];
  }
  if(isset($fav) && $fav['Save In'] != 'Default') {
    $dest = $fav['Save In'];
  } else if($config_values['Settings']['Deep Directories']) {
    $dest = get_deep_dir($dest, $tor_name);
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
    case 'folder':
      $return = folder_add_torrent($tor, $dest, $tor_name);
      break;
    default:
      _debug("Invalid Torrent Client: ".$config_values['Settings']['Client']."\n",-1);
      exit(1);
  }
  if($return === 0) {
    add_history($tor_name);
    _debug("Started: $tor_name in $dest\n",0);
    if(isset($fav))
      updateFavoriteEpisode($fav, $tor_name);
    if($config_values['Settings']['Save Torrents'])
      file_put_contents("$dest/$tor_name.torrent", $tor);
  } else {
    _debug("Failed Starting: $tor_name  Error: $return\n",-1);
  }
  return ($return === 0);
}
?>
