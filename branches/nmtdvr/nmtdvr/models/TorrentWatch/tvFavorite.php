<?php
class tvFavorite extends favorite {
  // Protected values can be updated with update()
  protected $episodes = '';
  protected $filter = '';
  protected $not = '';
  protected $quality = '';
  protected $onlyNewer = False;

  // Private data relating to last match
  private $recentEpisode = 0;
  private $recentSeason = 0;


  public function __construct($options) {
    parent::__construct($options);
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
        "\x00*\x00filter",
        "\x00*\x00not",
        "\x00*\x00quality",
        "\x00*\x00onlyNewer",
        "\x00tvFavorite\x00recentEpisode",
        "\x00tvFavorite\x00recentSeason"
    ));
  }

  public function __wakeup() {
    parent::__wakeup();
  }

  static protected function buildFilter() {
    $filter = parent::buildFilter();;
    $filter->add(new stringFilter());
    $filter->add(new episodeFilter());
    $filter->add(new onlyNewFilter());
    return $filter;
  }

  // Called every time a new feed item is started by this favorite
  // update our information about the most recent episode
  public function matched($feedItem) {
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
          return;
      }
    } else {
      // SxxExx based episode
      if($this->recentEpisode > 10000) {
        SimpleMvc::log('flipflop between date and episode based releases');
      } elseif($this->recentSeason > $feedItem->season OR
              ($this->recentSeason === $feedItem->season && $this->recentEpisode > $feedItem->episode)) {
        return;
      }
    }
    $this->changed       = True;
    $this->recentSeason  = $feedItem->season;
    $this->recentEpisode = $feedItem->episode;
  }

}

?>
