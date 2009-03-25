<?php

error_reporting(E_ALL|E_WARNING);
require_once('client.php');

// Hack
date_default_timezone_set('America/Los_Angeles');
DataCache::setStore(USERPATH.'DataCache/');

class TorrentWatch {
  var $client;
  var $config;
  var $favorites;
  var $feeds;
  var $history;

  public function __construct() {
    // Called whenever a feedItem recognizes a match with a favorite
    Event::add('nmtdvr.matchingFeedItem', array($this, 'matchingFeedItemCallback'));

    // Initialize all the pieces
    $this->config = TwConfig::getInstance();
    $this->favorites = favorites::getInstance();
    $this->feeds = feeds::getInstance();
    $this->history = history::getInstance();
    $this->client = $this->initClient();
  } 

  public function downloadFeedItem($feedUrl, $feedItem) {
    // If the feed has cookies set, transfer them to the link
    $link = $feedItem->link;
    if(($p = strpos($feedUrl, ':COOKIES:')) !== False) {
      $link .= substr($feedUrl, $p);
    }
    // Run the link through to the users client
    // title is passed incase the link needs to be saved to disk
    return ($this->client->addByUrl($link, $feedItem->title) === True);
  }

  function initClient() {
    switch($this->config->client) {
      case 'btpd':
        $client = new clientBTPD();
        break;
      case 'nzbget':
        $client = new clientNzbGet();
        break;
      case 'sabnzbd':
        $client = new clientSabNZBd();
        break;
      case 'trans1.22':
        $client = new clientTransmission122();
        break;
      case 'transRPC':
        $client = new clientTransmissionRPC();
        break;
      case 'folder':
        $client = new clientSimpleFolder();
        break;
      default:
        SimpleMvc::log('Invalid client while initializing: '.$this->config->client);
        $client = Null;
        break;
    }
    return $client;
  }

  public function matchingFeedItemCallback() {
    SimpleMvc::log(__FUNCTION__);
    list($feedItem, $feedId, $fav) = Event::$data;

    // incase another event already handled this
    if($feedItem->status !== 'noCallback') {
      return;
    }
    
    $feed = $this->feeds->get($feedId);

    if($this->downloadFeedItem($feed->url, $feedItem)) {
      $this->history->add($feedItem, $feed, $fav);
      $feedItem->status = 'automatedDownload';
    } else {
      $feedItem->status = 'failedStart';
    }
  }

}

?>
