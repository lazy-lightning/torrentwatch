<?php
/*
 * progressbar.php
 * updates the progress bar while feeds/favorites are being compared
 */

function display_progress_bar() {
  global $html_out;
  echo '<div class="dialog_window" id="progressDiv">';
  echo '<script type="text/javascript">showLayer("progressDiv");display("progressBar", 0, 1);setText("progressBar", "Loading Torrent Watch");</script></div>'."\n";
  ob_flush();
  flush();
}

function hide_progress_bar() {
  echo '<script type="text/javascript">hideLayer("progressDiv");</script>'."\n";
  ob_flush();
  flush();
}

function update_progress_bar($percent, $text = '') {
  echo '<script type="text/javascript">';
  if($percent > 0) 
    echo 'plus("progressBar", '.$percent.');';
  if($text != '')
    echo 'setText("progressBar", "'.$text.'");';
  echo '</script>'."\n";
  ob_flush();
  flush();
}

