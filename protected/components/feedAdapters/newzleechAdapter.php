<?php

// TODO:  method to skip items of small size(its stored in feeditem description)
class newzleechAdapter extends rssFeedAdapter {

  public $minItemSize;

  public function __construct($feed, $cache_location = null, $simplePie = 'newzleechSimplePie') 
  {
    parent::__construct($feed, $cache_location, $simplePie);
    $this->set_item_class('newzleechItem');
  }
}

class newzleechSimplePie extends SimplePie
{
  // Prune out any feed item that are too small
  public function get_items($start = 0, $end = 0) 
  {
    $minItemSize = array(
        0=>array(100, 'MB'), 
        720=>array(600, 'MB'), 
        1080=>array(2, 'GB'),
    );
    
    $out = array();
    $types = array('Byte'=>0, 'KB'=>1, 'MB'=>2, 'GB'=>3);

    foreach(parent::get_items($start, $end) as $item) 
    {
      $idx = 0;
      if(preg_match('/720p|1080[pi]/i', $item->get_title(), $quality))
      {
        if($quality[0][0] === '7')
          $idx = 720;
        else
          $idx = 1080;
      }
      $minSize = $minItemSize[$idx][0];
      $minType = $minItemSize[$idx][1];

      if(preg_match('/Size: (\d+)(?:,\d+)? (Byte|KB|MB|GB)/', $item->get_description(), $regs))
      {
        $type = $types[$regs[2]];
        $minType = $types[$minType];
        if($type > $minType  || ($type == $minType && $regs[1] > $minSize)) 
          $out[] = $item;
      }
      else
      {
        Yii::log('could not find size in newzleech description: '.$item->get_description(), CLogger::LEVEL_ERROR);
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
    list($foo, $id) = explode('=',parent::get_link(), 2);
    return "http://newzleech.com/?m=gen&dl=1&post=".$id;
  }
}
