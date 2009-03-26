<?php
class factory {
  static protected $feed = array(
  //  URL Pattern              Class
      '#newzleech.com/rss#' => 'newzleechFeed',
      '#.*#'                => 'rss',
  );

  static public function feed($url) {
    foreach(self::$feed as $pattern => $class) {
      if(preg_match($pattern, $url)) {
        return new $class(array('url' => $url));
      }
    }
  }

}

