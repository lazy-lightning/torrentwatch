<?php

error_reporting(E_ALL|E_WARNING);
require_once('client.php');

// Hack
date_default_timezone_set('America/Los_Angeles');
DataCache::setStore(USERPATH.'DataCache/');

// Hack to lock all reasources to a single instance
$file = '/tmp/nmtdvr.lock';
if(!file_exists($file))
  touch($file);
$_lock = fopen($file, 'a');

// Lock now, will be released when the script ends
// Should just block untill lock is made rather than throwing exception
if(flock($_lock, LOCK_EX) === False)
  throw new Exception();
unset($file);

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
    $this->client = factory::client();
  } 

  public function downloadFeedItem($feedUrl, $feedItem) {
    // If the feed has cookies set, transfer them to the link
    // Perhaps this should be done when the feeditem is created
    $link = $feedItem->link;
    if(($p = strpos($feedUrl, ':COOKIES:')) !== False) {
      $link .= substr($feedUrl, $p);
    }
    // Run the link through to the users client
    // title is passed incase the link needs to be saved to disk
    return ($this->client->addByUrl($link, $feedItem->title) === True);
  }

  public function matchingFeedItemCallback() {
    SimpleMvc::log(__FUNCTION__);
    list($feedItem, $fav) = Event::$data;

    // incase another event already handled this
    if($feedItem->status !== 'noCallback') {
      return;
    }
    
    $feed = $this->feeds->get($feedItem->feedId);

    if($this->downloadFeedItem($feed->url, $feedItem)) {
      $this->history->add($feedItem, $feed, $fav);
      $feedItem->status = 'automatedDownload';
    } else {
      $feedItem->status = 'failedStart';
    }
  }

}

?>
