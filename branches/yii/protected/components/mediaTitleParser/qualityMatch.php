<?php
class qualityMatch {
  public static $qual_reg =
      '(DVB|720p|DSR(ip)?|DVBRip|DVDR(ip)?|DVDScr|HR.HDTV|HDTV|HR.PDTV|PDTV|SatRip|SVCD|TVRip|WebRip|WS|1080[ip]|DTS|AC3|XViD|Blue?Ray|internal|limited|proper|repack|subbed|x264|iTouch)';

  public static function run($title)
  {
    $quality = array('Unknown');
    if(preg_match_all("/".self::$qual_reg."/i", $title, $regs)) 
    {
      // if 720p and hdtv strip hdtv to make hdtv more unique
      //
      $q = array_change_key_case(array_flip($regs[1]));
      if(isset($q['720p'], $q['hdtv'])) {
        unset($regs[1][$q['hdtv']]);
      }
      // FIXME: is this guaranteed an array? check reference
      if(is_array($regs[1]) && count($regs[1]) > 0)
        $quality = $regs[1];
      $shortTitle = trim(preg_replace("/".qualityMatch::$qual_reg.".*/i", "", $title), '- _.[]{}<>()@#$%^&*|\/;~`');
    }
    else
      $shortTitle = $title;
    return array($shortTitle, $quality);
  }
}
