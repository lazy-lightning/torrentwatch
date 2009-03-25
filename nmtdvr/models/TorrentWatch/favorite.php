<?php

class favorite extends cacheItem {
  // Protected values can be updated with update()
  protected $episodes = '';
  protected $feed = '';
  protected $filter = '';
  protected $name = '';
  protected $not = '';
  protected $quality = '';
  protected $saveIn = '';
  protected $seedRatio = -1;
  protected $onlyNewer = False;

  // Private data relating to last match
  private $recentEpisode = 0;
  private $recentSeason = 0;


  public function __construct($options) {
    parent::__construct($options);
    $this->initEvents();
  }

  public function __get($name) {
    if(property_exists($this, $name))
      return $this->$name;
    else
      return parent::__get($name);
  }

  public function __sleep() {
    return array_merge(parent::__sleep(), array(
        "\x00*\x00episodes",
        "\x00*\x00feed",
        "\x00*\x00filter",
        "\x00*\x00name",
        "\x00*\x00not",
        "\x00*\x00quality",
        "\x00*\x00saveIn",
        "\x00*\x00seedRatio", 
        "\x00favorite\x00recentEpisode",
        "\x00favorite\x00recentSeason"
    ));
  }

  public function __wakeup() {
    $this->initEvents();
    parent::__wakeup();
  }

  private function episodeFilter() {
    if($this->episodes != '') {
      // Split the filter(ex. 3x4-15 into 3,3 4,15).  @ to suppress error when no seccond item
      list($season, $episode) = explode('x',  $this->episodes, 2);
      @list($seasonLow,$seasonHigh) = explode('-', $season, 2);
      @list($episodeLow,$episodeHigh) = explode('-', $episode, 2);
      if(!isset($seasonHigh))
        $seasonHigh = $seasonLow;
      if(!isset($episodeHigh))
        $episodeHigh = $episodeLow;

      // Episode filter mis-match
      if(!($episodeLow <= $feedItem->episode && $feedItem->episode <= $episodeHigh)) {
        return False;
      }
      // Season filter mis-match
      if(!($seasonLow <= $feedItem->season && $feedItem->season <= $seasonHigh)) {
        return False;
      }
    }
    return True;
  }

  private function initEvents() {
    Event::add('nmtdvr.newFeedItem', array($this, 'newFeedItemCallback'));
  }

  // Return true or false if the given season/episode would be newer than the last
  // episode downloaded by this favorite
  private function onlyNewFilter($season, $episode) {
    if($this->onlyNewer === False)
      return True;

    if($season === 0 && !is_numeric($episode)) {
      // date based episodes
      if(($episode = strtotime($episode)) === False) {
        // Failed to convert episode to time, just accept
        return True;
      }
    }

    // seasons match, return true if given episode is greater
    // seasons dont match, return true if given season is greater
    return ($this->recentSeason == $season ? ($episode > $this->recentEpisode) : ($season > $this->recentSeason));
  }

  // Returns true or false if this favorite matches the specified feed Item
  public function isMatching($feedItem, $feedId) {
    $title = strtolower($feedItem->title);
    // Use the normalized short title against main filter where possible
    $shortTitle = strtolower(empty($feedItem->shortTitle) ? $title : $feedItem->shortTitle);

    if(!($this->feed == $feedId || strtolower($this->feed) == 'all' || $this->feed == '')) {
      SimpleMvc::log('feed mismatch');
      return False;
    }

    if(!$this->stringFilter($title, $shortTitle)) {
      SimpleMvc::log('filter/quality/not mismatch');
      return False;
    }

    if(!$this->episodeFilter()) {
      SimpleMvc::log('season/episode mismatch');
      $feedItem->status = 'filteredEpisode';
      return False;
    }

    if(!$this->onlyNewFilter($feedItem->season, $feedItem->episode)) {
      SimpleMvc::log('item not new');
      $feedItem->status = 'oldEpisode';
      return False;
    }

    SimpleMvc::log('full match');
    return True;
  }

  public function newFeedItemCallback() {
    list($feedItem, $feedId) = Event::$data;
    $feedItem->compareFavorite($this, $feedId);
  }

  private function stringFilter($title, $shortTitle) {
    // The main filter based on user matchstyle
    switch(TwConfig::getInstance()->matchStyle) {
      case 'simple':
      default:
        return (($this->filter != '' && strpos($shortTitle, strtolower($this->filter)) !== false) &&
         ($this->not == '' OR $this->my_strpos($title, strtolower($this->not)) === false) &&
         ($this->quality == 'All' OR $this->quality == '' OR $this->my_strpos($title, strtolower($this->quality)) !== false));
        break;
      case 'glob':
        return (($this->filter != '' && $this->fnmatch(strtolower('*'.$this->filter).'*', $shortTitle)) &&
         ($this->not == '' OR !$this->fnmatch('*'.strtolower($this->not).'*', $title)) &&
         ($this->quality == 'All' OR $this->quality == '' OR $this->fnmatch($title, '*'.strtolower($this->quality).'*') !== FALSE));
        break;
      case 'regexp':
        return (($this->filter != '' && preg_match('/'.strtolower($this->filter).'/', $shortTitle)) &&
         ($this->not == '' OR !preg_match('/'.strtolower($this->not).'/', $title)) &&
         ($this->quality == 'All' OR $this->quality == '' OR preg_match('/'.strtolower($this->quality).'/', $title)));
        break;
    }
  }

  // Called every time a new feed item is started by this favorite
  // update our information about the most recent episode
  public function updateRecent($feedItem) {
    if($feedItem->season === 0 && !is_numeric($feedItem->episode)) {
      // Date based episode
      $date = strtotime(strtr($feedItem->episode, '.', '/'));
      if($date === False) {
        SimpleMvc::log('unable to parse date string: '.$feedItem->episode);
        return;
      }

      if($this->recentEpisode < 10000) {
        SimpleMvc::log('flipflop between date and episode based releases');
      } elseif($this->recentEpisode >= $date) {
          return False;
      }
    } else {
      // SxxExx based episode
      if($this->recentEpisode > 10000) {
        SimpleMvc::log('flipflop between date and episode based releases');
      } elseif($this->recentSeason > $feedItem->season OR
              ($this->recentSeason === $feedItem->season && $this->recentEpisode > $feedItem->episode)) {
        return False;
      }
    }
    $this->changed       = True;
    $this->recentSeason  = $feedItem->season;
    $this->recentEpisode = $feedItem->episode;
  }

  // Custom strpos splits needle into space seperated tokens
  // returns True or False if one of those tokens is in the haystack
  private function my_strpos($haystack, $needle) {
    $pieces = explode(" ", $needle);
    foreach($pieces as $n) {
      if(strpos($haystack, $n) !== False)
        return True;
    }
    return False;
  }

  // RegExp re-implementation when fnmatch doesn't exist
  private function fnmatch($pattern, $string) {
    if(function_exists('fnmatch'))
      return fnmatch($pattern, $string);
    return @preg_match(
     '/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'),
     array('*' => '.*', '?' => '.?', '[' => '\[', ']' => '\]')) . '$/i', $string
    );
  }

  public function update($options) {
    parent::update($options);
    // Re-Compare to all the feed-items, but only if this item has been added
    // to the array(i.e. not from the constructor)
    if(is_numeric($this->id))
      Event::run('nmtdvr.updatedFavorite', $this);
    else
      SimpleMvc::log('Favorite not running update event, not fully initialized');
  }

}
?>
