<?php

// Attempt to clean the sorts of titles you see in usenet posts
// This will not work for all, but was tested against a newzleech
// search for hdtv and worked ok
class usenetItem extends feedAdapter_Item {

  private $cleanTitle;

  private function clean($string, $cleaners) {
    foreach($cleaners as $reg => $pos) {
      if(preg_match($reg, $string, $regs)) {
        return trim($regs[$pos]);
      }
    }
    return $string;
  }

  function get_title() {
    if($this->cleanTitle === null) {  
      $title = parent::get_title();
      $cleaners = array(
        '/&lt;(.*)&gt;/i' => 1,
        '/#[\w\d.]+@[\w\d.]+[\] ]-[\[ ](?:req \d+ -|[-\w .]+\]-\[)?([^\]]*)[\] ]?- ?\[?\d+\/\d+\]?/i' => 1,
        '/presents (.*) \[\d+ of \d+\] &quot;.*&quot;/i' => 1,
        '/\(([^):]+)\) \[\d+\/\d+\] - &quot;.*&quot;/i' => 1,
        '/^([\w\d.]+(?:-\w+)?) ?&quot;.*&quot;/i' => 1,
        '/^([A-Za-z0-9. ]+)&quot;.*&quot;/i' => 1,
        '/\[([^\]]+)\.(?:par2|part\d+\.rar|rar|r\d+|nzb|avi|mkv)\]/i' => 1,
        '/&quot;(.*)&quot;/i' => 1,
      );
      $postClean = array(
          '/^www.[\w\d-.]+ *(?:board request - )?(.*)$/i' => 1,
      );
      $newTitle = $this->clean($title, $cleaners);
      $newTitle = $this->clean($newTitle, $postClean);
      if($newTitle === $title)
        Yii::log('Failed cleaning: '.$title, CLogger::LEVEL_ERROR);
      $this->cleanTitle = $newTitle;
    }
    return $this->cleanTitle;
  }
}
