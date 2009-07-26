<?php

class mediaTitleParser {

  static protected $titleMatchers = array(
    'Full',
    'Date',
    'Partial',
    'Short',
    'Default',
  );

  static public function detect($title)
  {
    foreach(self::getMatchers() as $matcher)
    {
      $result = $matcher->run($title);
      if($result)
      {
        $result[] = qualityMatch::run($title);
        return $result;
      }
    }

    return null; // shouldn't happen, default matcher catches all
  }

  static public function getMatchers()
  {
    if(is_string(self::$titleMatchers[0]))
    {
      foreach(self::$titleMatchers as $index => $matcher)
      {
        $class = 'titleMatch'.$matcher;
        self::$titleMatchers[$index] = new $class;
      }
    }
    return self::$titleMatchers;
  }
}

class qualityMatch {
  public static $qual_reg =
      '(DVB|720p|DSR(ip)?|DVBRip|DVDR(ip)?|DVDScr|HR.HDTV|HDTV|HR.PDTV|PDTV|SatRip|SVCD|TVRip|WebRip|WS|1080[ip]|DTS|AC3|XViD|Blue?Ray|internal|limited|proper|repack|subbed|x264)';

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
      $quality = $regs[1];
    }
    return $quality;
  }
}

abstract class titleMatch {
  // Series title: string not including - or (
  // Episode title: optional, length is determined by the episode match
  public $title_reg = '^([^-\(]+)(?:.+)?';
  public $episode_reg = '';

  public $trFrom = '._-';
  public $trTo = '   ';
 
  abstract function foundMatch($title, $regs);

  function getRegExp()
  {
    return "/{$this->title_reg}{$this->episode_reg}/i";
  }

  public function run($title)
  {
    if(preg_match($this->getRegExp(), $title, $regs))
    {
      list($shortTitle, $season, $episode) = $this->foundMatch($title, $regs);
      $network = '';

      // Convert . and _ to spaces, and trim result
      $shortTitle = trim(strtr(str_replace("'", "&#39;", $shortTitle), $this->trFrom, $this->trTo));
      // Remove any marking of a second or third posting from the end of an item
      $shortTitle = trim(preg_replace('/\([23]\)$/', '', $shortTitle));
  
      // Custom handling for a few networks that show up as 'Foo.Channel.Show.Title.S02E02.Bar-ASDF'
      if(preg_match('/^([a-zA-Z]+\bchannel)\b(.*)/i', $shortTitle, $regs))
      {
        $network = $regs[1];
        $shortTitle = $regs[2];
      }
  
      return array($shortTitle, $season, $episode, $network);
    }
  }
}

class titleMatchFull extends titleMatch
{

  function __construct()
  {
    $this->episode_reg = 
           '\b('  // must be a word boundry before the episode to prevent turning season 13 into season 3
          .'S\d+[. _]?E\d+'        // S12E1 or S1.E22 or S4 E1
          .'|\d+x\d+'              // or 1x23
          .'|\d+[. ]?of[. ]?\d+)'; // or 03of18
  }

  function foundMatch($title, $regs)
  {
    Yii::log('episode match'.print_r($regs, TRUE));
    $shortTitle = trim($regs[1]);
    $episode_guess = trim(strtr($regs[2], $this->trFrom, $this->trTo));
    list($season,$episode) = explode('x', preg_replace('/(S(\d+) ?E(\d+)|(\d+)x(\d+)|(\d+) ?of ?(\d+))/i', '\2\4\6x\3\5\7', $episode_guess));

    return array($shortTitle, $season, $episode);
  }
}

class titleMatchDate extends titleMatch
{
  function __construct()
  {
    $this->episode_reg = 
           '\b('
          .'\d\d\d\d[- ._]\d\d[- _.]\d\d'.'|' // 2008-03-23
          .'\d\d[- _.]\d\d[- _.]\d\d\d\d'.'|' // 03.23.2008
          .'\d\d[- _.]\d\d[- _.]\d\d'         // 03 23 08
          .')';
  }

  function foundMatch($title, $regs)
  {
    Yii::log('date based episode '.print_r($regs, TRUE));
    $shortTitle = trim($regs[1]);

    $cleanDate = str_replace(' ', '/', trim(strtr($regs[2], $this->trFrom, $this->trTo)));
    // Use UTC for time measurements
    try
    {
      $date = new DateTime($cleanDate, 'UTC');
      $episode = $date->getTimestamp();
    }
    catch (Exception $e)
    {
      $episode = 0
      $shortTitle .= ' '.$regs[2];
    }

    Yii::log("season: $season episode: $episode shortTitle: $shortTitle");
    return array($shortTitle, 0, $episode);
  }
}

class titleMatchPartial extends titleMatch
{
  // only episode or season, not both
  public $episode_reg = '\b([SE])(\d+)\b';

  function foundMatch($title, $regs)
  {
    Yii::log('episode or season, not both'.print_r($regs, TRUE));
    $shortTitle = trim($regs[1]);
    $season  = $regs[2] == 'S' ? trim($regs[3]) : 1;
    $episode = $regs[2] == 'E' ? trim($regs[3]) : 0;
    return array($shortTitle, $season, $episode);
  }
}

class titleMatchShort extends titleMatch
{
  // three digits (four hits movie years, optional 0 to catch single digit season) with a
  // word boundry on each side, ex: some.show.402.hdtv
  // with at least some data after it to not match a group name at the end
  public $episode_reg = '\b(0?\d\d\d)\b..'; 
 
  function foundMatch($title, $regs)
  {
    Yii::log('3 digit season/episode identifier'.print_r($regs, TRUE));
    // 3 digit season/episode identifier
    $shortTitle = trim($regs[1]);
    $episode_guess = $regs[2];
    $episode = substr($episode_guess, -2);
    $season = ($episode_guess-$episode)/100;
    return array($shortTitle, $season, $episode);
  }
}

class titleMatchDefault extends titleMatch
{
  public $title_reg = '.';
  public $episode_reg = '';

  function foundMatch($title, $regs)
  {
    Yii::log('no match, strip quality');
    // No match, just strip everything after the quality
    $shortTitle = preg_replace("/".qualityMatch::$qual_reg.".*/i", "", $title);
    return array($shortTitle, 0, 0);
  }
}


