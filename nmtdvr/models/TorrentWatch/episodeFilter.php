<?php
class episodeFilter extends favFilterItem {
  static public function favFilter($favorite, $feedItem) {
    if($favorite->episodes == '') {
      return True;
    }

    // Split the filter(ex. 3x4-15 into 3,3 4,15).  @ to suppress error when no seccond item
    list($season, $episode) = explode('x',  $favorite->episodes, 2);

    @list($seasonLow,$seasonHigh) = explode('-', $season, 2);
    if(!isset($seasonHigh))
      $seasonHigh = $seasonLow;

    @list($episodeLow,$episodeHigh) = explode('-', $episode, 2);
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
    return True;
  }
}
