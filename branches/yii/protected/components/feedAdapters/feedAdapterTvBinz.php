<?php

// TvBinz translates spaces to underscores, which is all fine except they no longer match
// items detected from other usenet indexers, so change them back here

class feedAdapterTvBinz extends feedAdapter 
{
  public function __construct($feed, $cache_location = null, $cache_duration = null)
  {
    parent::__construct($feed, $cache_location, $cache_duration);
    $this->set_item_class('tvBinzItem');
  }
}

class tvBinzItem extends SimplePie_Item 
{
  function get_title()
  {
    return strtr(parent::get_title(), '_', ' ');
  }
}
