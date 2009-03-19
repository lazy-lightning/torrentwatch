<?php
// Return a formatted html link that will call javascript in a normal
// browser, and in the NMT browser

function setup_rss_list_html() {
  global $html_out;
  $html_out =  "<div id='torrentlist_container'>\n";
}
function finish_rss_list_html() {
  global $html_out;
  $html_out .=  "</div>\n";
}

function show_torrent_html($item, $feed, $alt) {
  global $html_out, $matched, $test_run;
  // add word-breaking flags after each period
  $title = preg_replace('/\./', '.&shy;', $item['title']);
  // prepare items for use in a url
  $utitle = rawurlencode($item['title']);
  // Copy feed cookies to item
  $ulink = get_torrent_link($item);
  if(($pos = strpos($feed, ':COOKIE:')) !== False) {
    $ulink .= substr($feed, $pos);
  }
  $ulink = rawurlencode($ulink);
  $feed = urlencode($feed);

  ob_start();
  require('templates/feed_item.tpl');
  $html_out .= ob_get_contents();
  ob_end_clean();
}

// The opening of the div which contains all the feeditems(one div per feed)
function show_feed_html($rss, $idx) {
  global $html_out;

  $html_out .= "<div class='feed' id='feed_$idx'><ul id='torrentlist' class='torrentlist'>";
  $html_out .= "<li class='header'>".$rss['title']."</li>\n";
}

// Closing the div which contains all the feed items
function close_feed_html() {
  global $html_out;
  $html_out .= '</ul></div>';
}

?>
