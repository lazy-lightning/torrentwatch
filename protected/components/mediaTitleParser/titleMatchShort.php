<?php
// This class matches a short(3 digit, no S or E identifiers) episode identifier in the title
class titleMatchShort extends titleMatch
{
  // three digits (four hits movie years, optional 0 to catch single digit season) with a
  // word boundry on each side, ex: some.show.402.hdtv
  // with at least some data after it to not match a group name at the end
  public $episode_reg = '\b(0?\d\d\d)\b..'; 
 
  function foundMatch($title, $regs)
  {
    // 3 digit season/episode identifier
    $shortTitle = trim($regs[1]);
    $episodeTitle = '';
    $end = strpos($title, $regs[0])+strlen($regs[0]);
    if($end < strlen($title))
      $episodeTitle = substr($title, $end);
    $episode_guess = $regs[2];
    $episode = substr($episode_guess, -2);
    $season = ($episode_guess-$episode)/100;
    return array($shortTitle, $episodeTitle, $season, $episode);
  }
}

