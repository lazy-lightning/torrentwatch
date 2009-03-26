<?php
class onlyNewFilter extends favFilterItem {
  // Return true or false if the given season/episode would be newer than the last
  // episode downloaded by this favorite
  static public function favFilter($favorite, $feedItem, $feedId) {
    if($favorite->onlyNewer === False)
      return True;

    $season = $feedItem->season;
    $episode = $feedItem->episode;

    if($season == 0 && !is_numeric($episode)) {
      // date based episodes
      if(($episode = strtotime($episode)) === False) {
        // Failed to convert episode to time, just accept
        return True;
      }
    }

    // seasons match, return true if given episode is greater
    // seasons dont match, return true if given season is greater
    return ($favorite->recentSeason == $season ? ($episode > $favorite->recentEpisode) : ($season > $favorite->recentSeason));
  }
}

