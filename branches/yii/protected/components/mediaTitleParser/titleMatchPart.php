<?php
// This class matches a full season and episode string found in the given title
class titleMatchPart extends titleMatch
{

  function __construct()
  {
    $this->episode_reg = 
           '\b('  // must be a word boundry before the episode to prevent turning season 13 into season 3
          .'\d+[. _]?of[. _]?\d+' // 03of18
          .'|part[._ ]?\d+(?:[. _]?of[. _]?\d+)?' // or part3
          .')\b';
  }

  function foundMatch($title, $regs)
  {
    $shortTitle = trim($regs[1]);
    $episodeTitle = '';
    $end = strpos($title, $regs[0])+strlen($regs[0]);
    if($end < strlen($title))
      $episodeTitle = substr($title, $end);

    $episode_guess = trim(strtr($regs[2], $this->trFrom, $this->trTo));
    $episode = (int) preg_replace('/(?:part[._ ]?(\d+)|(\d+)[. _]?of[. _]?\d+)/i', '\1\2', $episode_guess);
    return array($shortTitle, $episodeTitle, 1, $episode);
  }
}
