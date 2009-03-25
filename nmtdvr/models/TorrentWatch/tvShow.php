<?php
require_once('TVDB.php');

class tvShow extends cacheItem {
  var $episodes;
  var $shortTitle;
  var $changed;
  var $recentEpisodes;
  var $newEpisodes;
  var $lastMatch;
  var $newEpisode;

  var $banner;
  var $tvdbShow;

  function __construct($shortTitle) {
    $this->recentEpisodes = array();
    $this->newEpisodes = array();
    $this->changed = True;
    $this->shortTitle = strtr(strtolower($shortTitle), '._', '  ');
    $tvdbShows = TV_Shows::search($this->shortTitle);
    if(!$tvdbShows && stristr($this->shortTitle, 'and') !== False)
      $tvdbShows = TV_Shows::search(strtr(strtolower($this->shortTitle), array('and'=>'&')));
    if(!$tvdbShows) {
      file_put_contents('/tmp/twlog', "Could not get a matching show for: $shortTitle\n", FILE_APPEND);
      return;
    }
    $this->tvdbShow = $tvdbShows[0];
    $bannerFile = 'images/banners/'.preg_replace("/[^\.\-\s_a-zA-Z\d]/","",$tvdbShows[0]->seriesName).'.jpg';
    if(file_exists($bannerFile))
      $this->banner = $bannerFile;
    else if(!empty($this->tvdbShow->banner)) {
      $img = file_get_contents('http://thetvdb.com/banners/'.$this->tvdbShow->banner);
      if($img) {
        $this->banner = $bannerFile;
        file_put_contents($this->banner, $img);
      }
    }
  }

  function __wakeup() {
    $this->changed = False;
    // Shows are considered to have a new episode for 2 days since it was added
    if($this->newEpisode && ($this->newEpisode['addTime']-time()) > 60*60*24*2) {
      $this->newEpisode = False;
      $this->changed = True;
    }
  }

  function addEpisodeLink($feedItem) {
    if(!($feedItem instanceof feedItem)) {
      return False;
    }

    $season = $feedItem->season;
    $episode = $feedItem->episode;
    if(isset($this->episodes[$season][$episode]))
      return; // Should we save the new link? Probably a different quality of same episode
    if($feedItem->pubDate > $this->lastMatch);
     $this->lastMatch = $feedItem->pubDate;
    $newEpisode = array();
    $newEpisode['title'] = $feedItem->title;
    $newEpisode['link'] = $feedItem->link;
    $newEpisode['new'] = false;
    if($this->tvdbShow) {
      $tvdbEpisode = $this->tvdbShow->getEpisode($season, $episode);
      if(!empty($tvdbEpisode)) {
        $newEpisode['tvdbEpisode'] = $tvdbEpisode;
        // anything airing in the last 3 days is considered new
        $newEpisode['new'] = $tvdbEpisode->firstAired > (time()-60*60*24*4);
        if($newEpisode['new']) {
          $this->newEpisode =& $newEpisode; // reference will be maintained through serialize/unserialize
        }
      }
    }
    $newEpisode['addTime'] = time();
    $newEpisode['season'] = $season;
    $newEpisode['episode'] = $episode;
    $this->changed = True;
    $this->episodes[$season][$episode] = &$newEpisode;
    if($episode['new']) {
      $this->newEpisodes[] = &$newEpisode;
      if(count($this->newEpisodes) >= 5)
        array_shift($this->newEpisodes);
    }

    // Maintain a list of the last 5 episodes seen per show
    // The references should be maintained through serialize/unserialize
    $this->recentEpisodes[] = &$newEpisode;
    if(count($this->recentEpisodes) >= 5)
      array_shift($this->recentEpisodes);
  }

}


  
