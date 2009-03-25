<?php
class feeds extends cachedArray {

  private static $instance;

  function __construct() {
    parent::__construct('feed');
  }

  public function __sleep() {
    return parent::__sleep();
  }

  public function compareFavorite($fav) {
    foreach($this->array as $feed)
      $feed->compareFavorite($fav);
  }

  public function del($idx) {
    $feed = $this->get($idx);
    if(parent::del($idx)) {
      // Invalidate the feedItem array
      $feed->resetFeedItems();
      unset($feed);
      return True;
    }
    return False;
  }

  public function getFeedItem($feedId, $feedItemId) {
    $feed = $this->get($feedId);
    if(!empty($feed)) {
      return $feed->getFeedItem($feedItemId);
    }
    return False;
  }

  public static function getInstance() {
    if(self::$instance == NULL) {
      self::$instance = new feeds();
    }
    return self::$instance;
  }

  public function resetFeedItems() {
    foreach($this->get() as $feed)
      $feed->resetFeedItems();
  }

  public function update() {
    foreach($this->get() as $feed)
      $feed->updateItems();
  }

}
