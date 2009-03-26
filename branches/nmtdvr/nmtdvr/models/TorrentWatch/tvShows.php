<?php
require_once('tvShow.php');

class tvShows extends cachedArray {
  function __construct() {
    parent::__construct('tvShows', __CLASS__);
    Event::add('nmtdvr.newFeedItem', array($this, 'newFeedItemCallback'));
  }

  function isValidArrayItem($obj) {
    if($obj instanceof tvShow)
      return parent::isValidArrayItem($obj);
    return False;
  }

  // needs more rethinking to prevent duplicates
  function addEpisode($feedItem) {
    $show = $this->get($feedItem->shortTitle);
    if(!$show) {
      $show = new tvShow($feedItem->shortTitle);
      if(!$this->add($show)) {
        return False;
      }
    }
    if($feedItem->season > 0 && $feedItem->episode > 0)
      $show->addEpisodeLink($feedItem);
  }

  function newFeedItemCallback() {
    $this->addEpisode(Event::$data[0]);
  }

}
