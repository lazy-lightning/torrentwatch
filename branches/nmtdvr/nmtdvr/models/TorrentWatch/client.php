<?php
// Client Class
// Minimum inheritance must define addByFile();
// must also setup $client_name and $defauls then call
// parent::__construct() in own constructor

abstract class client {

  var $error;
  var $config;
  var $client_name;

  abstract public function addByFile($filename, $name = False);

  function __construct($options) {
    // Strip client from begining of class name
    $this->client_name = substr(get_class($this), 6);
    // Get the configuration reference 
    $this->getConfig();
    // loop through and initialize any unset keys
    foreach($options as $key => $data) {
      if(!isset($this->config[$key])) {
        $this->config[$key] = $data;
        // Ugly hack to tell the config to save itself
        TwConfig::getInstance()->update();
      }
    }
  }

  function __wakeup() {
    $this->getConfig();
  }

  // Generic addByUrl function passes on to addByFile
  // Can be re-implemented by clients to add directly
  // through client, but you will lose private tracker cookie support
  function addByUrl($url, $name = False) {
    $be = new browserEmulator();
    $data = $be->file_get_contents($url);
    if(!$data)
      return 'Could not fetch item';

    $tempnam = tempnam('torrentwatch', '');
    file_put_contents($tempnam, $data);
    $ret = $this->addByFile($tempnam, $name);
    @unlink($tempnam);
    return $ret;
  }

  function getConfig() {
    $config = TwConfig::getInstance();
    $this->config =& $config->getClientOptions($this->client_name);
  }

  function execClient($cmd, $options) {
    if(!file_exists($cmd))
      return "client executable does not exist: $cmd";
    exec($cmd.' '.$options, $output, $return);
    if($return == 0)
      return True;

    return "$cmd exited with return status of $return";
  }

}

class clientSabNZBd extends client {

  function __construct() {
    parent::__construct(array(
        'host' => '127.0.0.1',
        'port' => '8080',
        'api' => 'sabnzbd/api'
    ));
  }

  function addByURL($url, $name = False) {
    SimpleMvc::log(__CLASS__."->".__FUNCTION__.": $url");
    $host = $this->config['host'];
    $port = $this->config['port'];
    $api  = $this->config['api'];

    $request="http://$host:$port/$api?mode=addurl&name=".urlencode($url);

    $contents = @file_get_contents($request);
    if($contents) {
      return True;
    } else {
      return "Failed to connect with SabNZBd at $host:$port/$api";
    }
  }

  function addByFile($filename, $name = False) {
    return 'addByFile not supported with SabNZBd';
  }
} 

class clientTransmissionRPC extends client {

  function __construct() {
    parent::__construct(array(
        'host' => '127.0.0.1',
        'port' => '9091',
        'uri'  => '/transmission/rpc',
        'seed-ratio' => '-1'
    ));
  }

  function addByFile($filename, $name = False) {
    // pass on to addByUrl().  browserEmulator will return local files too
    return $this->addByUrl($filename, $name);
  }

  function addByUrl($url, $name = False) {
    $be = new browserEmulator();
    $data = @$be->file_get_contents($url);
    if($data)
      return $this->addByData($data);

    return 'Could not fetch file';
  }
    

  function addByData($data) {
    // Setup Variables
    $config == TwConfig::getInstance();
    $dest = $config->downloadDir;
    $seedRatio = $this->config['seedRatio'];

    // transmission dies with bad folder if dest doesn't end in a /
    if(substr($dest, strlen($dest)-1, 1) != '/')
      $dest .= '/';

    $args = array('download-dir' => $dest, 
                  'metainfo'     => base64_encode($data));
    if($seedRatio != "" && $seedRatio >= 0)
      $args['ratio-limit'] = $seedRatio;

    return $this->rpc('torrent-add', $args);
  }

  function rpc($method, $arguments) {
    $result = $this->sendRequest(array('method' => $method, 'arguments' => $arguments));

    if(isset($responce['result']) AND ($responce['result'] == 'success' or $responce['result'] == 'duplicate torrent'))
      return True;
    if(isset($responce['result']))
      return "Transmission RPC Error: ".print_r($responce);

    return "Failure connecting to Transmission >= 1.30";
  }

  function sendRequest($request) {
 
    $host = $this->config['host'];
    $port = $this->config['port'];
    $uri = $this->config['uri'];

    $be = new browserEmulator();
    $be->addPostData(json_encode($request));
    $be->addHeader('Content-Type', 'application/json');
    $be->addHeader('Connection', 'Close');
    return $be->file_get_contents("http://$host:$port/$uri");
  }

}

class clientBTPD extends client {

  function __construct() {
    parent::__construct(array(
        'btcli'   => '/mnt/syb8634/bin/btcli',
        'add'     => 'add -d',
        'connect' => '-d /share/.btpd/',
        'webAdmin' => ':8883/torrent/bt.cgi'
    ));
  }

  function addByFile($filename, $name = False) {
    $config = TwConfig::getInstance();
    $dest = $config->downloadDir;
    $btcli = $this->config['btcli'];
    $btcli_add = $this->config['add'];
    $btcli_connect = $this->config['connect'];
    $btcli_exec="$btcli $btcli_connect";

    return $this->execClient($btcli_exec, $btcli_add." ".escapeshellarg($dest)." ".escapeshellarg($filename));
  }
}

class clientTransmission122 extends client {

  function __construct() {
    parent::__construct(array(
        'remote' => '/mnt/syb8634/bin/transmission-remote',
        'connect' => '-g /share/.transmission/',
        'webAdmin' => ':8077/'
    ));
  }

  function addByFile($filename, $name = False) {
    $trans_remote = $this->config['remote'];
    $trans_connect = $this->config['connect'];
    $trans_exec = "$trans_remote $trans_connect";
    $trans_add = '-a';

    $this->execClient($trans_exec, $trans_add.' '.escapeshellarg($filename));
  }
}

class clientNzbGet extends client {

  function __construct() {
    parent::__construct(array(
        'nzb_exec' => '/mnt/syb8634/bin/nzbget',
        'connect'  => '-c /share/.nzbget/nzbget.conf',
        'webAdmin' => ':8066/'
    ));
  }

  function addByFile($filename, $name = False) {
    $nzb_exec = $this->config['nzb_exec'];
    $nzb_connect = $this->config['connect'];
    $nzb_append = "-A ".escapeshellarg($filename);

    $this->execClient($nzb_exec, $nzb_connect.' '.$nzb_append);
  }
}

class clientSimpleFolder extends client {

  function __construct() {
    parent::__construct(array(
        'folder' => '/tmp/',
        'webAdmin' => '/'
    ));
  }

  function addByFile($filename, $name = False) {
    $c = TwConfig::getInstance();
    if(empty($name))
      $name = basename($filename);
    $outfile = $c->downloadDir.'/'.$name.'.'.$c->fileExtension;
    if(file_exists($outfile)) {
      $i = 1;
      $tmp = $outfile.'.'.$i;
      while(file_exists($tmp)) {
        $tmp = $outfile.'.'.$i++;
      }
      $outfile = $tmp;
    }
    $status = copy($filename, $outfile);
    SimpleMvc::log("$filename  To ".$outfile);
    SimpleMvc::log(__CLASS__."->".__FUNCTION__.": status ".print_r($status,True));
    return $status;
  }
}

?>
