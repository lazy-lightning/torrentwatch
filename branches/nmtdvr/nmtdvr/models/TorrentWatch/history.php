<?php

class history extends cachedArray {

  // epHistory is a multi-dimensional array: [ShortTitle][Season][Episode]
  private $epHistory;

  public function __construct() {
    $this->initEvents();
    $data = DataCache::get('history', 'epHistory');
    if($data)
      $this->epHistory = &$data;
    else 
      $this->epHistory = array();
    parent::__construct('historyItem');
  }

  public function __destruct() {
    if($this->changed)
      DataCache::put('history', 'epHistory', 31270000, $this->epHistory);
    parent::__destruct();
  }

  public static function getInstance() {
    static $instance;
    if($instance == NULL) {
      $instance = new history();
    }
    return $instance;
  }

  public function add($feedItem, $feed = NULL, $fav = NULL) {
    if($feedItem instanceof historyItem) {
      $item = $feedItem;
    } else {
      // This should provably be put elsewhere
      $options = array(
          'title'      => $feedItem->title,
          'shortTitle' => $feedItem->shortTitle,
          'season'     => $feedItem->season,
          'episode'    => $feedItem->episode,
          'feedId'     => $feedItem->feedId,
          'feedTitle'  => feeds::getInstance()->get($feedItem->feedId)->title,
      );

      if($fav !== NULL) {
        $options['favId'] = $fav->id;
        $options['favName'] = $fav->name;
      }

      $item = new historyItem($options);
    }

    if(($id = parent::add($item)) !== FALSE) {
      if(!(empty($item->season) || empty($item->episode) || empty($item->shortTitle)))
        $this->epHistory[strtolower($item->shortTitle)][$item->season][$item->episode] = $id;
      return $id;
    }
    return False;
  }

  function del($uniqueId) {
    return False; // only add, not remove from history
  }

  private function initEvents() {
    // These events set the status on a new item, and double check it on match before
    // any other events fire
    Event::add_first('nmtdvr.newFeedItem', array($this, 'updateFeedItemCallback'));
    Event::add_first('nmtdvr.matchingFeedItem', array($this, 'updateFeedItemCallback'));
  }

  public function updateFeedItemCallback() {
    $feedItem = Event::$data[0];
    SimpleMvc::log(__CLASS__.'::'.__FUNCTION__);

    if($this->downloadedTitle($feedItem->title))
      $feedItem->status = 'previouslyDownloaded';
    else if($this->downloadedEpisode($feedItem->shortTitle, $feedItem->season, $feedItem->episode))
      $feedItem->status = 'duplicateEpisode';
  }

  // returns true if the season/episode/shorttitle are in the epHistory array
  function downloadedEpisode($shortTitle, $season, $episode) {
    if(empty($shortTitle))
      return False;

    if($season == 0 && $episode == 0) {
        return False;
    }

    $shortTitle = strtolower($shortTitle);

    // season but no episode info
    if($episode === 0 && isset($this->epHistory[$shortTitle][$season]))
      return False;

    return isset($this->epHistory[$shortTitle][$season][$episode]);
  }

  // returns true if the title is in the history items array
  function downloadedTitle($title) {
    if($this->get($title, 'title'))
      return True;
    return False;
  }

  function emptyArray() {
    parent::emptyArray();
    $this->epHistory = array();
    $this->changed = True;
  }

}
?>
