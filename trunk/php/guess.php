<?php
function guess_match($title, $normalize = FALSE) { 
  // Main regexp
  $reg1 ='/^';
  // Series Title 
  $reg1.='([^-\(]+)'; // string not including - or (
  $reg1.='(?:.+)?'; // optinally followed by a title, length is determined by the episode match
  // Episode 
  $reg1.='\b(';  // must be a word boundry before the episode
  $reg1.='S\d[. ]?+E\d+'.'|';  // S12E1
  $reg1.='\d+x\d+' .'|';  // 1x23
  $reg1.='\d+[. ]?of[. ]?\d+'.'|';  // 03of18
  $reg1.='[\d -.]{10}';   // 2008-03-23 or 07.23.2008 or .20082306. etc
  $reg1.=')/i';

  // Quality
  $quality ='/(DVB'  .'|';
  $quality.='DSRIP'  .'|';
  $quality.='DVBRip' .'|';
  $quality.='DVDR'   .'|';
  $quality.='DVDRip' .'|';
  $quality.='DVDScr' .'|';
  $quality.='HR.HDTV'.'|';
  $quality.='HDTV'   .'|';
  $quality.='HR.PDTV'.'|';
  $quality.='PDTV'   .'|';
  $quality.='SatRip' .'|';
  $quality.='SVCD'   .'|';
  $quality.='TVRip'  .'|';
  $quality.='WebRip' .'|';
  $quality.='720p'   .'|';
  $quality.='1080i'  .'|';
  $quality.='1080p)/i';

  if(preg_match($reg1, $title, $regs)) {
    $episode_guess = trim($regs[2]);
    $key_guess = str_replace("'", "&#39;", trim($regs[1]));
    if(preg_match($quality, $title, $qregs))
      $data_guess = str_replace("'", "&#39;", trim($qregs[1]));
    else
      $data_guess = '';
  } else
    return False;
  if($normalize == TRUE) {
    // Convert . and _ to spaces, and trim result
    $from = "._";
    $to = "  ";
    $key_guess = trim(strtr($key_guess, $from, $to));
    $data_guess = trim(strtr($data_guess, $from, $to));
    $episode_guess = trim(strtr($episode_guess, $from, $to));
    // Standardize episode output to SSxEE, strip leading 0
    // This is (b|c|d) from earlier.  If it is style e there will be no replacement, only strip leading 0
    $episode_guess = ltrim(preg_replace('/(S(\d+) ?E(\d+)|(\d+)x(\d+)|(\d+) ?of ?(\d+))/i', '\2\4\6x\3\5\7', $episode_guess), '0');
  }
  return array("key" => $key_guess, "data" => $data_guess, "episode" => $episode_guess);
}

function guess_feedtype($feedurl) {
  global $config_values;
  $be = new browserEmulator();
  $content = $be->file($feedurl);
  // Should be on the second line, but test the first 5 incase
  // of doctype etc.
  for($i = 0;$i < 5;$i++) {
    if(preg_match('/<feed xml/', $content[$i], $regs))
      return 'Atom';
    else if (preg_match('/<rss/', $content[$i], $regs))
      return 'RSS';
  }
  return "Unknown";
}

function guess_atom_torrent($summary) {
  $wc = '[\/\:\w\.\+\?\&\=\%\;]+';
  // Detects: A HREF=\"http://someplace/with/torrent/in/the/name\"
  if(preg_match('/A HREF=\\\"(http'.$wc.'torrent'.$wc.')\\\"/', $summary, $regs)) {
    _debug("guess_atom_torrent: $regs[1]\n",2);
    return $regs[1];
  } else {
    _debug("guess_atom_torrent: failed\n",2);
  }
  return FALSE;
}

?>
