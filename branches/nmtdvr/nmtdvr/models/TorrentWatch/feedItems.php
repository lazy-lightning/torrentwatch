<?php
class feedItems extends cachedArray {

  function __construct($url) {
    $this->url = $url;
    // Use multiple feedItem arrays, based on url
    parent::__construct('feedItem', $url);

    // Receive an event when a favorite changes to recompare all the items
    // Could be moved up into feedItem class
    Event::add('nmtdvr.updatedFavorite', array($this, 'updatedFavoriteCallback'));
    // Receive an event what a favorite is deleted to reset the history of
    // all matching items
    Event::add('nmtdvr.deletedFavorite', array($this, 'deletedFavoriteCallback'));
  }

  function __sleep() {
    return parent::__sleep();
  }

  function add($newArrayItem) {
    SimpleMvc::log(__CLASS__."->".__FUNCTION__.'()');
    if(parent::add($newArrayItem)) {
      $feed = feeds::getInstance()->get($this->url, 'url');
      $data = array($newArrayItem);
      Event::run('nmtdvr.newFeedItem', $data);
    }
  }

  function compareFavorite($fav, $feedId) {
    $start = microtime(TRUE);
    foreach($this->get() as $feedItem) {
      $feedItem->compareFavorite($fav, $feedId);
      SimpleMvc::log(microtime(TRUE)-$start);
    }
    SimpleMvc::log(microtime(TRUE)-$start);
  }

  function deletedFavoriteCallback() {
    $favId = Event::$data;
    foreach($this->get($favId, 'matchingFavorite') as $feedItem) {
      $feedItem->resetHistory($favId);
    }
  }

  function updatedFavoriteCallback() {
    $fav = Event::$data;
    // Verify we have the right kind of event data, just in case
    if($fav instanceof favorite)
      $this->compareFavorite($fav, feeds::getInstance()->get($this->url, 'url')->id);
    else
      SimpleMvc::log(__FUNCTION__.': received bad Event::$data');
  }

}

