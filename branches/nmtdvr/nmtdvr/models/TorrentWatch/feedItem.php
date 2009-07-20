<?php
class feedItem extends cacheItem {

  // Valid item status, const instead?
  private static $statusWhitelist = array(
      'nomatch',
      'oldEpisode',
      'duplicateEpisode',
      'previouslyDownloaded',
      'automatedDownload',
      'manualDownload',
      'failedStart',
      'noCallback',
  );

  // Raw feed data
  private $feedId;
  private $title = '';
  private $link = '';
  private $pubDate = '';
  private $description = '';

  // detected from title
  private $shortTitle = '';
  private $season = '';
  private $episode = '';
  private $quality = '';

  // Related to favorite matching
  private $matchingFavorite = '';
  private $status = 'nomatch';

  function __construct($options) {
    $this->title = isset($options['title']) ? $options['title'] : '';
    $this->link = isset($options['link']) ? $options['link'] : '';
    $this->pubDate = isset($options['pubDate']) ? $options['pubDate'] : '';
    $this->description = isset($options['description']) ? $options['pubDate'] : '';
    $this->feedId = isset($options['feedId']) ? $options['feedId'] : '';

    // Try and parse some data out of the title
    $guess = guess::episodeDataFromTitle($this->title, True);
    if($guess) {
      $this->shortTitle = $guess['shortTitle'];
      $this->quality = $guess['quality'];
      $this->season = $guess['season'];
      $this->episode = $guess['episode'];
    }

    parent::__construct(array());
  }

  function __get($name) {
    if(property_exists($this, $name)) {
      return $this->$name;
    }
    return parent::__get($name);
  }

  function __sleep() {
    return array_merge(parent::__sleep(), array(
      "\x00feedItem\x00feedId",
      "\x00feedItem\x00title",
      "\x00feedItem\x00link",
      "\x00feedItem\x00pubDate",
      "\x00feedItem\x00description",
      "\x00feedItem\x00shortTitle",
      "\x00feedItem\x00season", 
      "\x00feedItem\x00episode",
      "\x00feedItem\x00quality",
      "\x00feedItem\x00matchingFavorite",
      "\x00feedItem\x00status"
    ));
  }

  function __wakeup() {
    parent::__wakeup();
  }

  // This gets called when a favorite is updated or deleted
  function resetHistory($favId = '') {
    // If this is the same favorite that matched before
    if($this->matchingFavorite !== $favId) {
      return;
    }

    // If the status isn't downloaded, then reset the item.
    // Otherwise just change the status
    SimpleMvc::log('Resetting match to id '.$favId);
    if(strpos($this->status, 'Download') === False) {
      // Wasn't downloaded
      $this->matchingFavorite = '';
      $this->setStatus('nomatch');
    } else {
      $this->setStatus('previouslyDownloaded');
    }
  }

  // This function is the common denominator to compare and start
  // an item between adding new feedItems and updating a favorite
  function compareFavorite($fav) {
    SimpleMvc::log("Comparing {$fav->name} with {$this->title}");

    $favId = $fav->id;
    if(empty($favId) && $favId !== 0) {
      SimpleMvc::log(__FUNCTION__.': shouldnt get here, uninitialized favorite');
      return False;
    }

    // If this item has been previously matched, and not by this favorite 
    if($this->matchingFavorite !== '' && $this->matchingFavorite !== $fav->id)
      return;

    // Reset any prior history with this favorite
    $this->resetHistory($favId);

    // Do the actual comparison in the favorite
    if(!$fav->isMatching($this))
      return False;

    // reset item from previous or manual to automated download
    if(strpos($this->status, 'Download') !== False)
      $this->setStatus('automatedDownload');

    // Only start items of un-determined status
    // comes after isMatching() to allow above status revert
    if($this->status !== 'nomatch')
      return False;

    // Feed Item is now verified downloadable.  Record which fav matched and 
    // set status to noCallback to indicate if the following event fails to update it
    SimpleMvc::log('Found matching favorite: '.$fav->name);
    $this->matchingFavorite = $fav->id;
    $this->setStatus('noCallback');

    // To be picked up by the history to initiate download
    $data = array($this, $fav);
    Event::run('nmtdvr.matchingFeedItem', $data);

    // If the callback initated a download let the favorite know
    if(strpos($this->status, 'Download') !== False)
      $fav->matched($this);

    return True;
  }

  function setStatus($status) {
    if(in_array($status, self::$statusWhitelist)) {
      $this->changed = True;
      $this->status = $status;
    } else {
      SimpleMvc::log('Invalid status passed to feedItem: '.$status);
    }
  }

}
