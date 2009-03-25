<?php
class TwConfig {

  private $cacheDir;
  private $changed;
  private $client;
  private $clientOptions;
  private $cron;
  private $downloadDir;
  private $fileExtension;
  private $firstRun;
  private $matchStyle;
  private $onlyNewer;
  private $saveTorrents;
  private $verifyEpisode;
  private $watchDir;

  public function __construct() {
    $data = DataCache::Get("global", "config");
    if($data) {
      $this->changed = False;
      $this->cacheDir = $data->cacheDir;
      $this->client = $data->client;
      $this->clientOptions = $data->clientOptions;
      $this->cron = $data->cron;
      $this->downloadDir = $data->downloadDir;
      $this->fileExtension = $data->fileExtension;
      $this->firstRun = False;
      $this->matchStyle = $data->matchStyle;
      $this->onlyNewer = $data->onlyNewer;
      $this->saveTorrents = $data->saveTorrents;
      $this->verifyEpisode = $data->verifyEpisode;
      $this->watchDir = $data->watchDir;
    } else { // Defaults
      $basedir = '/home/xbmc/.torrents_NEW';
      $this->changed = True;
      $this->cacheDir = $basedir.'/new_cache/';
      $this->client = 'btpd';
      $this->clientOptions = array();
      $this->cron = '/etc/cron.hourly';
      $this->downloadDir = '/home/xbmc/';
      $this->fileExtension = 'torrent';
      $this->firstRun = True;
      $this->matchStyle = 'simple';
      $this->onlyNewer = False;
      $this->saveTorrents = True;
      $this->verifyEpisode = True;
      $this->watchDir = $basedir;
    }
  }

  public function __wakeup() {
    $this->changed = False;
  }

  public function __destruct() {
    if($this->changed)
      DataCache::Put("global", "config", 31270000, $this);
  }

  public function __get($name) {
    if(property_exists($this, $name))
      return $this->$name;

    throw new Exception();
  }

  public function __set($name, $value) {
    throw new Exception();
  }

  public function & getClientOptions($client) {
    if(!isset($this->clientOptions[$client]))
      $this->clientOptions[$client] = array();
    
    return $this->clientOptions[$client];
  }

  public function update($options = array()) {
    $this->changed = True;
    if(count($options) === 0)
      return;

    /* key is the variables name in TwConfig class
       data is the variables name in the html form */
    $input = array('downloadDir'     => 'downdir',
                   'watchDir'        => 'watchdir',
                   'client'          => 'client',
                   'matchStyle'      => 'matchstyle',
                   'onlyNewer'       => 'onlynewer',
                   'fileExtension'   => 'extension');
    $checkboxs = array('verifyEpisode' => 'verifyepisodes',
                       'saveTorrents'  => 'savetorrents',
                       'onlyNewer'     => 'onlynewer');

    foreach($input as $key => $data)
      if(isset($options[$data]))
        $this->$key = $options[$data];

    foreach($checkboxs as $key => $data) {
      if(isset($options[$data])) 
        $this->$key = True;
      else
        $this->$key = False;
    }

    // Client options
    foreach($options as $key => $value) {
      if(strpos($key, 'client_') !== 0) {
        continue;
      }

      list($prefix, $client, $option) = explode('_', $key, 3);

      if(isset($this->clientOptions[$client][$option])) {
        $this->clientOptions[$client][$option] = $value;
      } else {
      }
    }
    return;
  }

  static public function getInstance() {
    static $instance;
    if(!is_object($instance))
      $instance = new TwConfig;
    return $instance;
  }
}
