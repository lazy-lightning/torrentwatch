<?php
// TODO: this class is fairly ugly, fix it
class qualityMatch {
  public static $qual_reg =
      '\b(DVB|720p|DSR(ip)?|DVBRip|DVDR(ip)?|DVDScr|HR.HDTV|HDTV|HR.PDTV|PDTV|SatRip|SVCD|TVRip|WebRip|WS|1080[ip]|DTS|AC3|XViD|Blue?Ray|internal|limited|proper|repack|subbed|x264|iTouch|telesync|dvd5|int|ntsc|rarfix|pal|festival)\b';

  public static $strip_reg =
    '\b(subpack|complete|rerip|stv)\b';

  public static function run($title)
  {
    $quality = array('Unknown');
    if(preg_match_all("/".self::$qual_reg."/i", $title, $regs)) 
    {
      // if 720p and hdtv strip hdtv to make hdtv more unique
      $q = array_change_key_case(array_flip($regs[1]));
      if(isset($q['720p'], $q['hdtv'])) {
        unset($regs[1][$q['hdtv']]);
      }
      if(count($regs[1]) > 0)
        $quality = $regs[1];
      $shortTitle = preg_replace("/".self::$qual_reg.".*/i", "", $title);
    }
    else
      $shortTitle = $title;

    $shortTitle = preg_replace("/".self::$strip_reg.".*/i", "", $shortTitle);
    return array(trim($shortTitle, ' \'"- _.[]{}<>()@#$%^&*|\/;~`'."\t\n"), $quality);
  }
}
