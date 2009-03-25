<?php

// Making class abstract caused issues with the unit tests
class feed extends cacheItem {

  private $title;
  private $description;
  private $url;
  private $lastUpdate;

  protected $minFeedUpdate = 900; // 15 Minutes

  private $feedItems;

  protected function updateReal() { }

  public function __construct($options) {
    SimpleMvc::log(__CLASS__."::".__FUNCTION__);
    parent::__construct($options);
    $this->url = $options['url'];
    $this->lastUpdate = 0;
    $this->feedItems = new feedItems($this->url);
  }

  public function __get($name) {
    if(property_exists($this, $name)) {
      return $this->$name;
    }
    return parent::__get($name);
  }

  public function __sleep() {
    unset($this->feedItems); // trigger feedItem serialize
    return array_merge(parent::__sleep(), array(
        "\x00feed\x00title",
        "\x00feed\x00description",
        "\x00feed\x00url",
        "\x00feed\x00lastUpdate",
        "\x00*\x00minFeedUpdate",
    ));
  }

  public function __wakeup() {
    parent::__wakeup();
    $this->feedItems = new feedItems($this->url);
  }

  protected function addFeedItem($feedItem) {
    $this->feedItems->add($feedItem);
  }

  public function compareFavorite($fav) {
    $this->feedItems->compareFavorite($fav, $this->id);
  }

  public function getFeedItem($feedItemId = '') {
    return $this->feedItems->get($feedItemId);
  }

  public function resetFeedItems() {
    $this->feedItems->emptyArray();
    DataCache::Put('feedItem', $this->url, -1, null);
    $this->lastUpdate = 0;
    $this->updateItems();
  }

  public function needsUpdate() {
    return (time() > $this->lastUpdate + $this->minFeedUpdate);
  }

  public function updateItems() {
    SimpleMvc::log(__CLASS__."::".__FUNCTION__);
    if($this->needsUpdate()) {
      SimpleMvc::log('needsUpdate');
      if($this->updateReal()) {
        SimpleMvc::log('updateReal succeded');
        $this->lastUpdate = time();
        $this->changed = True;
        return True;
      } else SimpleMvc::log('updateReal Failed');
    } else SimpleMvc::log('no update needed');
    return False;
  }

}

?>
