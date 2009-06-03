<?php

require_once('SimplePie_1.1.3/simplepie.inc');

class feedAdapter extends SimplePie {
  // the feed active record class the adapter is to work with
  private $_feedAR;

  function __construct($feed, $cache_location = null, $cache_duration = null) {
    parent::SimplePie(null, $cache_location, $cache_duration);
    $this->_feedAR = $feed;
    $this->set_feed_url($feed->url);
    // feedAdapter_File implements the :COOKIE: portion of url's
    $this->set_item_class('feedAdapter_Item');
    $this->set_file_class('feedAdapter_File');
  }

  function init() {
    parent::init();

    if($this->error()) {
      $this->_feedAR->status = feed::STATUS_ERROR;
      Yii::log("Error loading feed {$this->_feed->title}: ".$this->error());
      return False;
    }

    $this->_feedAR->status = feed::STATUS_OK;
    $this->_feedAR->title = $this->get_title();
    $this->_feedAR->description = $this->get_description();

    $items = $this->get_items();
    Yii::log(count($items)." items to consider", CLogger::LEVEL_ERROR);
    foreach($items as $item) {
      Yii::log("Testing item: ".$item->get_title(), CLogger::LEVEL_ERROR);
      $hash = md5($item->get_id());
      if(false === feedItem::model()->exists('hash=:hash', array(':hash'=>$hash))) {
        Yii::log("Creating item from ".get_class($item), CLogger::LEVEL_ERROR);
        feedItem::factory(array(
              'hash'        => $hash,
              'feed_id'     => $this->_feedAR->id,
              'imdbId'      => $item->get_imdbId(),
              'title'       => $item->get_title(),
              'url'         => $item->get_link(),
              'description' => $item->get_description(),
              'pubDate'     => $item->get_date('U'),
        ));
      }
    }
  }
}

// attempt to detect imdb from description, can be overridden for a better match
class feedAdapter_Item extends SimplePie_Item {
  function get_imdbId() {
    $desc = $this->get_description();
    if(preg_match('/imdb.com\/title\/tt(\d+)/i', $desc, $regs))
      return $regs[1];
    else
      return 0;
  }
}

class feedAdapter_File extends SimplePie_File {
  function feedAdapter_File($url, $timeout = 10, $redirects = 5, $headers = null,
                            $useragent = null, $force_fsockopen = false) {
    // pretend for the sake of tvbinz.net
    $useragent = 'UniversalFeedParser/4.01 +http://feedparser.org/';

    Yii::log("Starting url: $url", CLogger::LEVEL_ERROR);
    // Translate :COOKIE: into http headers
    if($cookies = stristr($url, ':COOKIE:')) {
      $url = rtrim(substr($url, 0, -strlen($cookies)), "&");
      $headers['Cookie'] = '$Version=1; '.strtr(substr($cookies, 8), '&', ';');
    }
    // convert &amp; into & because simplepie_file preserves it?
    $url = str_replace('&amp;', '&', $url);
    if($headers !== null)
      Yii::log("Final url: $url\nHeaders:  \n".implode("\n", $headers), CLogger::LEVEL_ERROR);
    parent::SimplePie_File($url, $timeout, $redirects, $headers, $useragent, $force_fsockopen);
    file_put_contents('/tmp/'.__FUNCTION__, serialize($this));
  }
}

