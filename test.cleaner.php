<?php
function get_title($title) {
  $title = trim($title);
  echo "Starting: $title\n";
  $title = clean($title);
  $title = postclean($title);
  echo "Finished: $title\n\n";
}

function postclean($title) {
  $cleaners = array(
      '/^www.[\w\d-.]+ *(?:board request - )?(.*)$/i' => 1,
  );

  $new = realClean($title, $cleaners);
  return $new;
}

function clean($title) {
  $cleaners = array(
      '/&lt;(.*)&gt;/i' => 1,
      '/#[\w\d.]+@[\w\d.]+[\] ]-[\[ ](?:req \d+ -)?([^\]]*)[\] ]?- ?\[?\d+\/\d+\]?/i' => 1,
      '/presents (.*) \[\d+ of \d+\] &quot;.*&quot;/i' => 1,
      '/\(([^):]+)\) \[\d+\/\d+\] - &quot;.*&quot;/i' => 1,
      '/^([\w\d.]+(?:-\w+)?) ?&quot;.*&quot;/i' => 1,
      '/^([A-Za-z0-9. ]+)&quot;.*&quot;/i' => 1,
      '/&quot;(.*)&quot;/i' => 1,
  );
  $new = realClean($title, $cleaners);
  return $new;
}

function realClean($title, $cleaners) {
    foreach($cleaners as $reg => $pos) {
      if(preg_match($reg, $title, $regs)) {
        return trim($regs[$pos]);
      }
    }
    return $title;
  }

$titles = file('test.Titles');
foreach($titles as $title) {
  get_title($title);
}
