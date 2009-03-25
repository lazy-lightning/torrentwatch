<?php
class atom extends feed {

  var  $atom;


  function __sleep() {
    return array_merge(array("atom"), parent::__sleep());
  }

  protected function updateReal() {
    $this->atom = new myAtomParser($this->url);
    $data = $this->atom->getRawOutput();
    if(!$data) {
      $this->error = 'Error with myAtomParser';
      return False;
    }
    $newest = end($this->feedItems->array);
    $skip = True;
    foreach(array_reverse($data) as $atomItem) {
      // First Determine if this item is actualy new
      if($skip && !empty($newest)) {
        if($this->getLink($atomItem) == $newest['link'] )
          $skip = False;
        continue;
      }
      // Item is confirmed new, add it
      $this->addItem($atomItem['title'], $this->getLink($atomItem), $atomItem['pubDate'], $atomItem['description']);
    }
    return True;
  }

  function getLink($atomItem) {
      if(stristr($atomItem['id'], 'torrent')) // torrent link in id
        $link = $atomItem['id'];
      else // torrent hidden in summary
        $link = $this->guess($atomItem['summary']);
    return html_entity_decode($link);
  }

  private function guess($summary) {
    $wc = '[\/\:\w\.\+\?\&\=\%\;]+';
    // Detects: A HREF=\"http://someplace/with/torrent/in/the/name\"
    if(preg_match('/a href=\\\"(http'.$wc.'torrent'.$wc.')\\\"/i', $summary, $regs)) {
      return $regs[1];
    }
    return False;
  }
}

