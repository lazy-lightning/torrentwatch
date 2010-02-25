<?php

require_once('simplepie.inc');

class feedAdapter_File extends SimplePie_File {
  function feedAdapter_File($url, $timeout = 10, $redirects = 5, $headers = null,
                            $useragent = null, $force_fsockopen = false) {

    // pretend for the sake of a particular private feed
    if($useragent === null || substr($useragent, 0, 9) === 'SimplePie')
      $useragent = 'UniversalFeedParser/4.01 +http://feedparser.org/';

    // Translate :COOKIE: into http headers
    if($cookies = stristr($url, ':COOKIE:')) {
      $url = rtrim(substr($url, 0, -strlen($cookies)), "&");
      $headers['Cookie'] = '$Version=1; '.strtr(substr($cookies, 8), '&', ';');
    }
    // convert &amp; into & because simplepie_file preserves it?
    $url = str_replace('&amp;', '&', $url);

    parent::SimplePie_File($url, $timeout, $redirects, $headers, $useragent, $force_fsockopen);
  }
}

