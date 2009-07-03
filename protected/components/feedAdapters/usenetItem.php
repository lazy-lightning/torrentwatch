<?php

// Attempt to clean the sorts of titles you see in usenet posts
// This will not work for all, but was tested against a newzleech
// search for hdtv and worked ok
class usenetItem extends feedAdapter_Item {

  private $cleanTitle;

  private function clean($string, $cleaners) {
    foreach($cleaners as $reg => $pos) {
      if(preg_match($reg, $string, $regs)) {
        $string = trim($regs[$pos]);
        if(!is_numeric($string))
          return $string;
      }
    }
    return $string;
  }

  function get_orig_title() {
    return parent::get_title();
  }

  function get_title() {
    if($this->cleanTitle === null) {  
      $title = parent::get_title();
      $cleaners = array(
          '/#[\w\d.]+@[\w\d.]+[\] ]-[\[ ](?:req \d+ -|[-\w .]+\]-\[)?([^\]]*)[\] ]?- ? ?\[?\d+\/\d+\]?/i' => 1,
          '/presents (.*) \[\d+ of \d+\] ".*"/i' => 1,
          '/\(([^):]+)\) \[\d+\/\d+\] - ".*"/i' => 1,
          '/^([\w\d.]+(?:-\w+)?) ?".*"/i' => 1,
          '/^([A-Za-z0-9. ]+)".*"/i' => 1,
          '/\[([^\]]+)\.(?:par2|part\d+\.rar|rar|r\d+|nzb|avi|mkv)\]/i' => 1,
          '/>[^>]+>([^<]+)<[^<]+< \(\d+\/\d+\)/i' => 1,
          '/"(.*)"/i' => 1,
      );
      $postClean = array(
          '/^www.[\w\d-.]+ *(?:board request - )?(.*)$/i' => 1,
      );
      $newTitle = $this->clean($this->clean($title, $cleaners), $postClean);
      if($newTitle === $title)
        Yii::log('Failed cleaning: '.$title, CLogger::LEVEL_WARNING);
      $this->cleanTitle = $newTitle;
    }
    return $this->cleanTitle;
  }
}
