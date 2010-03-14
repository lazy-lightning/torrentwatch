<?php

// TvBinz translates spaces to underscores, which is all fine except they no longer match
// items detected from other usenet indexers, so change them back here

class tvBinzAdapter extends rssFeedAdapter 
{
  public function __construct($feed, $cache_location = null, $simplePie = null)
  {
    parent::__construct($feed, $cache_location, $simplePie);
    $this->simplePie->set_item_class('tvBinzItem');
  }
}

class tvBinzItem extends feedAdapter_Item 
{
  public function get_title()
  {
    return strtr(parent::get_title(), '_', ' ');
  }
}
