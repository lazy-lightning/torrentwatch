<?php
// This class matches a title that has a dated episode as oposed to season/episode
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

  function fakeErrorHandler() { return False; }

  function foundMatch($title, $regs)
  {
    $shortTitle = trim($regs[1]);
    $episodeTitle = '';
    $end = strpos($title, $regs[0])+strlen($regs[0]);
    if($end < strlen($title))
      $episodeTitle = substr($title, $end);

    $episode = false;

    $cleanDate = str_replace(' ', '/', trim(strtr($regs[2], $this->trFrom, $this->trTo)));
    // Use UTC for time measurements
    // php issues a warning, which yii exits on, and an exception,
    // on bad input so temporarily replace the error handler.
    $handler = set_error_handler(array($this, 'fakeErrorHandler'));
    try
    {
      $date = new DateTime($cleanDate, new DateTimeZone('UTC'));
      $episode = $date->format('U');
    }
    catch (Exception $e)
    {
      $date = null;
    }
    restore_error_handler($handler);
    return $date === null ? false : array($shortTitle, $episodeTitle, 0, $episode);
  }
}
