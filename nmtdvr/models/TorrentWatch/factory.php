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

  static protected $clients = array(
   // client option   Class
      'btpd'      => 'clientBTPD',
      'nzbget'    => 'clientNzbGet',
      'sabnzbd'   => 'clientSabNzbd',
      'trans1.22' => 'clientTransmission122',
      'transRPC'  => 'clientTransmissionRPC',
      'folder'    => 'clientSimpleFolder',
  );

  static public function client() {
    $client = TwConfig::getInstance()->client;
    if(isset(self::$clients[$client])) {
      return new self::$clients[$client];
    }
    SimpleMvc::log('Invalid client while initializing: '.$client);
    return NULL;
  }

}

