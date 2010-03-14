<?php
/**
 * titleMatch is the base class from which all methods to 
 * detect title information from an input string are based.
 */
// TODO: returning arrays isn't the way objects should work.
// make all the array variables either public vars or protected vars
// with getters/__get and return true/false 
abstract class titleMatch {
  // Series title: string not including - or (
  // Episode title: optional, length is determined by the episode match
  public $title_reg = '^([^-\(]+)(?:.+)?';
  public $episode_reg = '';

  public $trFrom = '._-';
  public $trTo = '   ';
 
  abstract public function foundMatch($title, $regs);

  public function getRegExp()
  {
    return "/{$this->title_reg}{$this->episode_reg}/i";
  }

  // if the regular expression matches and the implementing classes
  // foundMatch function likes the results clean it up and 
  // return the resulting data

  public function run($title)
  {
    if(preg_match($this->getRegExp(), $title, $regs) &&
       false !== ($opts = $this->foundMatch($title, $regs)))
    {
      list($shortTitle, $episodeTitle, $season, $episode) = $opts;
      $network = '';

      // Convert . and _ to spaces, and trim result
      $shortTitle = trim(strtr(str_replace("'", "&#39;", $shortTitle), $this->trFrom, $this->trTo));
      $episodeTitle = trim(strtr(str_replace("'", "&#39;", $episodeTitle), $this->trFrom, $this->trTo));
      // Remove any marking of a second or third posting from the end of an item
      $shortTitle = trim(preg_replace('/\([23]\)$/', '', $shortTitle));
      $episodeTitle = trim(preg_replace('/\([23]\)$/', '', $episodeTitle));
  
      // Custom handling for a few networks that show up as 'Foo.Channel.Show.Title.S02E02.Bar-ASDF'
      if(preg_match('/^([a-zA-Z]+\bchannel)\b(.*)/i', $shortTitle, $regs))
      {
        $network = $regs[1];
        $shortTitle = $regs[2];
      }
  
      return array($shortTitle, $episodeTitle, $season, $episode, $network);
    }
    return false;
  }
}
