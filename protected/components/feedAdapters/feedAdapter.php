<?php

require_once('SimplePie_1.1.3/simplepie.inc');
require_once('feedAdapter_File.php');

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
      if(empty($this->_feedAR->title)) {
        // new record
        $this->_feedAR->title = 'Error initializing feed';
      }
      return False;
    }

    $this->_feedAR->status = feed::STATUS_OK;
    $this->_feedAR->title = $this->get_title();
    $this->_feedAR->description = $this->get_description();

    $items = $this->get_items();
    foreach($items as $item) {
      $hash = md5($item->get_id());
      if(false === feedItem::model()->exists('hash=:hash', array(':hash'=>$hash))) {
        factory::feedItemByAttributes(array(
              'hash'        => $hash,
              'feed_id'     => $this->_feedAR->id,
              'downloadType'=> $this->_feedAR->downloadType,
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

class feedAdapter_Item extends SimplePie_Item {
  // attempt to detect imdb from description, can be overridden for a better match
  function get_imdbId() {
    $desc = $this->get_description();
    if(preg_match('/imdb.com\/title\/tt(\d+)/i', $desc, $regs))
      return $regs[1];
    else
      return 0;
  }

  function get_title() {
    return html_entity_decode(parent::get_title());
  }

  function get_description() {
    return html_entity_decode(parent::get_description());
  }
}

