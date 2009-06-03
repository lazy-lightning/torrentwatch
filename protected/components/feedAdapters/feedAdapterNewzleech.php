<?php

// TODO:  method to skip items of small size(its stored in feeditem description)
class feedAdapterNewzleech extends feedAdapter {
  public function __construct($feed, $cache_location = null, $cache_duration = null) 
  {
    parent::__construct($feed, $cache_location, $cache_duration);
    $this->set_item_class('newzleechItem');
  }

  // Prune out any feed item less than 100 MB
  public function get_items($start = 0, $end = 0) {
    $items = parent::get_items($start, $end);
    $out = array();

    foreach($items as $item) {
      preg_match('/Size: (\d+)(?:.\d+)? (KB|MB|GB)/', $item->get_description(), $regs);
      if($regs[2] == 'GB' || ($regs[2] == 'MB' && $regs[1] > 100)) {
        $out[] = $item;
      } else {
        Yii::log('Skipping item, too small: '.$regs[1].' '.$regs[2], CLogger::LEVEL_ERROR);
      }
    }

    return $out;
  }
}

// translate to proper download links, and use usenetItem to clean the titles
class newzleechItem extends usenetItem {
  // Translate to a download link instead of details link
  // the link in the feed looks like:
  //    http://newzleech.com/?p=12345678
  // we want
  //    http://newzleech.com/?m=gen&dl=1&post=12345678
  function get_link() {
    $link = parent::get_link();
    list($foo, $id) = explode('=',$link, 2);
    return "http://newzleech.com/?m=gen&dl=1&post=".$id;
  }
}
