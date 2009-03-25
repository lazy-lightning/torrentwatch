<?php
class guess {
  public static function episodeDataFromTitle($title, $normalize = False) {
    $titleGuess = '';
    $episodeGuess = $seasonGuess = 0;
          // Series Title
    $reg1 ='^([^-\(]+)' // string not including - or ( or [
          .'(?:.+)?' // optinally followed by a title, length is determined by the episode match
                     // no explaniation why above is better than .*(it might not, cant remember why)
          // Episode
          .'\b('  // must be a word boundry before the episode
          .'S\d+ ?E\d+'.'|' // S12E1
          .'\d+x\d+' .'|' // 1x23
          .'\d+ ?of ?\d+'.'|' // 03of18
          .'part ?\d+(?: ?of ?\d+)?'.'|' // part4 or part4of6
          .'[\d -.]{10})';  // 2008-03-23 or 07.23.2008 or .20082306. etc

    // Quality
    $quality ='(DVB|WS|DSRIP|DVBRip|DVDR|DVDRip|DVDScr|HR.HDTV|'.
              'HDTV|HR.PDTV|PDTV|SatRip|SVCD|TVRip|WebRip|'.
              '720p|1080i|1080p)';

    // Apply main regexp
    if(preg_match("/$reg1/i", $title, $regs)) {
      // strip any foreign characters from the guesses
      $episodeGuess = trim($regs[2]);
      $titleGuess = str_replace("'", "&#39;", trim($regs[1], ".- \t\n\r\0\x0B"));

      // Attempt to detect the quality
      if(preg_match("/$quality/i", $title, $qregs))
        $qualityGuess = str_replace("'", "&#39;", trim($qregs[1]));
      else
        $qualityGuess = '';

      // Seperate season and episode from episodeGuess
      //                  1       2     3     4     5                    6
      if(preg_match('/(?:S(\d+) ?E(\d+)|(\d+)x(\d+)|(\d+) ?of ?\d+|part ?(\d+)(?: ?of ?\d+)?)/i', $episodeGuess, $regs)) {
        // Test the start item of each | group above, skipping 0 as it is the full match
        foreach(array(6, 5, 3, 1) as $i) {
          if(isset($regs[$i])) {
            if($i >= 5) {
              // part X of Y, so just do s01eXX
              $seasonGuess = 1;
              $episodeGuess = (int)$regs[$i];
            } else {
              $seasonGuess = (int)$regs[$i];
              $episodeGuess = (int)$regs[$i+1];
            }
            break;
          }
        }
      }
    } else if(preg_match('/^([^[-]+)(?:.+)?\b'.$quality.'+/i', $title, $regs)) {
      // Similar regexp to above, but with no episode matching
      $titleGuess = trim($regs[1]);
      $qualityGuess = trim($regs[2]);
      $seasonGuess = 0;
      $episodeGuess = 0;
    } else // Couldn't guess
      return False;

    // why?
    while(preg_match("/^(.*)$quality/i", $titleGuess, $regs))
      $titleGuess = $regs[1];

    if($normalize == TRUE) {
      // Convert . and _ to spaces, and trim result
      $from = "._";
      $to = "  ";
      $titleGuess = trim(strtr($titleGuess, $from, $to));
      $qualityGuess = trim(strtr($qualityGuess, $from, $to));
    }

    return array
    (
     "shortTitle" => $titleGuess,
     "quality" => $qualityGuess,
     "season" => $seasonGuess,
     "episode" => $episodeGuess
    );
  }

}
