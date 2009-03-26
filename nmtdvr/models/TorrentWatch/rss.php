<?php
class rss extends feed {

  // Used to determine which items are new in a feed
  private $latestItemId = null;

  function __sleep() {
    return array_merge(parent::__sleep(), array(
        "\x00rss\x00latestItemId"
    ));
  }

  // adds a new feed item to the array
  function addRssItem($rssItem) {
      $this->addFeedItem(new feedItem(array
      (
       'title'       => $rssItem['title'], 
       'link'        => $this->getLink($rssItem), 
       'pubDate'     => empty($rssItem['pubDate']) ? time() : $rssItem['pubDate'], 
       'description' => $rssItem['description']
      )));
  }

  function getFeed() {
    // setup $data and make sure its valid
    $lastRss = $this->setupLastRss();
    $data = $lastRss->Get($this->url);
    if(!$data) {
      SimpleMvc::log("Error starting rss parser.");
      SimpleMvc::log($this);
      return False;
    }

    if(empty($data['items'])) {
      SimpleMvc::log("No items in rss feed: ".$this->url);
      return False;
    }
    return $data;
  }

  // Returns the content link(.torrent or .nzb) inside an rssItem
  protected function getLink($rssItem) {
    if(isset($rssItem['enclosure']['url']))
      $link = $rssItem['enclosure']['url'];
    else {
      if(isset($rssItem['link'])) {
        $link = $rssItem['link'];
      } else {
        SimpleMvc::log("couldn't find link\n".print_r($rssItem, TRUE));
        return False;
      }
    }
    return html_entity_decode($link);
  }

  function getNewItems($allItems) {
    // Find the oldest new item.  Remember items start with newest first
    $i = 0;
    foreach($allItems as $rssItem) {
      if($this->latestItemId === $this->getRssItemId($rssItem)) {
        $i--; // Remove self from valid items and break out
        break;
      }
      $i++;
    }

    // No new items will leave $i === -1
    if($i === -1) {
      return False;
    }

    SimpleMvc::log('Items to add: '.$i);
    return array_slice($data['items'], 0, $i);
  }

  // returns a hopefully unique identifier of this rss item
  function getRssItemId($rssItem) {
    return empty($rssItem['guid']) ? $this->getLink($rssItem) : $rssItem['guid'];
  }

  function resetFeedItems() {
    $this->latestItemId = null;
    parent::resetFeedItems();
  }

  // Returns a prepared lastRss object
  function setupLastRSS() {
    $lastRss = new lastRSS();
    $lastRss->stripHTML = True;
    $lastRss->date_format = 'U';
    return $lastRss;
  }

  // performs the Full Update
  protected function updateReal() {
    SimpleMvc::log(__FUNCTION__);

    if(($items = $this->getFeed()) === False) {
      return False;
    }

    // Get any unknown feed information from the feed
    // NOTE: these wont get saved if there are no new items, shouldnt matter
    // dont overwrite title incase user changed it
    if(empty($this->title) && !empty($data['title']))
      $this->title = $data['title'];
    if(!empty($data['description']))
      $this->description = $data['description'];

    // Hardcoded for now, should be changeable.  Maybee by age instead of count
    $this->feedItems->setMaxItems($data['items_count']);

    if(($items = $this->getNewItems($data['items'])) === False) {
      SimpleMvc::log('No items to add');
      return True;
    }

    // Reverse the array so as to start with the oldest item and add them
    foreach(array_reverse($items) as $rssItem)
      $this->addRssItem($rssItem);

    // get the first(newest) item off the array and save its itemId
    $this->latestItemId = $this->getRssItemId(reset($items));

    return True;
  }

}

