<?php
  // Return a formatted html link that will call javascript in a normal
  // browser, and in the funky NMT browser
  function _jscript($func, $contents) {
    if($_SERVER["REMOTE_ADDR"] == '127.0.0.1') {
      return('<a href=# onclick="'.$func.';return false;">'.$contents.'</a>');
    } else {
      return('<a href="javascript:'.$func.'">'.$contents.'</a>');
    }
  }

  function setup_rss_list_html() {
    global $html_out, $html_header;
    $html_header = "<div class=feedlist>\n";
    $html_out =  "<div id='torrentlist_container'>\n";
  }
  function finish_rss_list_html() {
    global $html_out, $html_header;
    $html_header .="</div>\n";
    $html_out .=  "</div>\n";
  }

  function show_torrent_html($item, $feed, $alt) {
    global $html_out, $matched, $test_run;

    $feed = urlencode($feed);
    $html_out .= "<li class='torrent match_$matched $alt' title='"._isset($item, 'description')."'>";
    $html_out .= "<a class='context_link' href='tw-iface.cgi?mode=matchtitle&rss=$feed&title=".rawurlencode($item['title'])."'></a>";
    $html_out .= "<a class='context_link' href='tw-iface.cgi?mode=dltorrent&title=".rawurlencode($item['title'])."&link=".rawurlencode(get_torrent_link($item))."'></a>";
    $html_out .= "<div class='torrent_name'>".$item['title']."</div>";
    $html_out .= "<div class='torrent_pubDate'>"._isset($item, 'pubDate').'</div>';
    $html_out .= "</li>\n";
  }

  function show_feed_html($rss, $idx) {
    global $html_out;

    $html_out .= "<div class='feed' id='feed_$idx'><ul id='torrentlist' class='torrentlist'>";
    $html_out .= "<li class='header'>".$rss['title']."</li>\n";
  }

  function close_feed_html() {
    global $html_out;
    $html_out .= '</ul></div>';
  }

?>
