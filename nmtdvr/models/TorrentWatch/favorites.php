<?php
class favorites extends cachedArray {

  private static $instance;

  function __construct() {
    parent::__construct('favorite');
  }

  public function sleep() {
    return parent::__sleep();
  }

  public function add($fav) {
    if(False !== ($idx = parent::add($fav))) {
      // Blank update to trigger the global compare
      $fav->update(array());
    }
    return $idx;
  }

  public function del($favId) {
    if(parent::del($favId)) {
      // Event to tell the feeditems that were matched by this
      // to reset
      Event::run('nmtdvr.deletedFavorite', $favId);
    }
  }

  public static function getInstance() {
    if(self::$instance == NULL) {
      self::$instance = new favorites();
    }
    return self::$instance;
  }

  function compareFeedItem($feedItem) {
    foreach($this->array as $fav)
      if($feedItem->compareFavorite($fav, $feedItem->feedId))
        return True;
    return False;
  }

}

