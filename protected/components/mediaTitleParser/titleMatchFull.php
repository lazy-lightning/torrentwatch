<?php
// This class matches a full season and episode string found in the given title
class titleMatchFull extends titleMatch
{

  function __construct()
  {
    $this->episode_reg = 
           '\b('  // must be a word boundry before the episode to prevent turning season 13 into season 3
          .'S\d+[. _]?E(?:P ?)?\d+'        // S12E1 or S1.E22 or S4 EP 1
          .'|\d[. _]?+x[. _]?\d+'              // or 1x23
          .'|\d+[. _]?of[. _]?\d+)'; // or 03of18
  }

  function foundMatch($title, $regs)
  {
    $shortTitle = trim($regs[1]);
    $episodeTitle = '';
    $end = strpos($title, $regs[0])+strlen($regs[0]);
    if($end < strlen($title))
      $episodeTitle = substr($title, $end);

    $episode_guess = trim(strtr($regs[2], $this->trFrom, $this->trTo));
    list($season,$episode) = explode('x', preg_replace('/(S(\d+)[. _]?E(?:P ?)?(\d+)|(\d+)[_ .]?x[_ .]?(\d+)|(\d+)[. _]?of[. _]?(\d+))/i', '\2\4\6x\3\5\7', $episode_guess));

    return array($shortTitle, $episodeTitle, $season, $episode);
  }
}
