<?php
abstract class favorite extends filter {
  // Common propertys between favorites
  protected $feed = '';
  protected $name = '';
  protected $seedRatio = -1;
  protected $saveIn = '';


  // Called by feedItem if this favorite initiated a download
  // use this to record information about the last item if neccessary
  abstract function matched($feedItem);

  public function __construct($options) {
    parent::__construct($options);
    $this->initEvents();
  }

  public function __get($name) {
    return parent::__get($name);
  }

  public function __sleep() {
    return array_merge(parent::__sleep(), array(
        "\x00*\x00feed",
        "\x00*\x00name",
        "\x00*\x00seedRatio",
        "\x00*\x00saveIn",
    ));
  }

  public function __wakeup() {
    $this->initEvents();
    parent::__wakeup();
  }

  static protected function buildFilter() {
    $filter = new chain();
    $filter->add(new feedFilter());
    return $filter;
  }

  private function initEvents() {
    Event::add('nmtdvr.newFeedItem', array($this, 'newFeedItemCallback'));
  }

  public function isMatching($feedItem, $feedId) {
    return $this->runFilter(array($feedItem, $feedId));
  }

  public function newFeedItemCallback() {
    list($feedItem, $feedId) = Event::$data;
    $feedItem->compareFavorite($this, $feedId);
  }

  public function setFeed($value) {
    if(!is_numeric($value)) {
      SimpleMvc::log('feed must be a numeric id: '.$value);
      return False;
    }
    $this->feed = $value;
  }

  public function setSaveIn($value) {
    if(!is_dir($value)) {
      SimpleMvc::log('Invalid directory to save in: '.$value);
      return False;
    }

    $this->saveIn = $value;
    return True;
  }

  public function update($options) {
    parent::update($options);
    // Re-Compare to all the feed-items, but only if this item has been added
    // to an array(i.e. not from the constructor)
    if(is_numeric($this->id)) {
      Event::run('nmtdvr.updatedFavorite', $this);
    } else
      SimpleMvc::log('Favorite not running update event, not fully initialized');
  }

}
