<?php
// This class matches a title that has only a season or episode marker
// This is common for documentries with no season, or special features 
// that dont have episode numbers but relate to a given season
class titleMatchPartial extends titleMatch
{
  // only episode or season, not both
  public $episode_reg = '\b(S|EP?)[ _.]?(\d+)\b';

  function foundMatch($title, $regs)
  {
    $shortTitle = trim($regs[1]);
    $episodeTitle = '';
    $end = strpos($title, $regs[0])+strlen($regs[0]);
    if($end < strlen($title))
      $episodeTitle = substr($title, $end);
    $regs[2] = strtolower($regs[2]);
    $season  = $regs[2] == 's' ? trim($regs[3]) : 1;
    $episode = $regs[2][0] == 'e' ? trim($regs[3]) : 0;
    return array($shortTitle, $episodeTitle, $season, $episode);
  }
}
