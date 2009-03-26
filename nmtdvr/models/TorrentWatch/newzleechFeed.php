<?php

class newzleechFeed extends rss {
  protected function addRssItem($rssItem) {
    // Clean up the title
    // not implemented yet
    parent::addRssItem($rssItem);
  }

  protected function getLink($rssItem) {
   $tmp = explode('=', parent::getLink($rssItem));
   return 'http://newzleech.com/?m=gen&dl=1&post='.$tmp[1];
  }

}

