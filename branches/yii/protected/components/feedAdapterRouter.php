<?php

// This is a simple class for choosing between various available feedAdapters
// to add a new adapter just insert the class name and a regexp to match against the url
// in the $adapters array
// NOTE:  Perhaps would be better to break the hostname out of the url and match up routers
//        with particular hostnames

class feedAdapterRouter {

  static protected $adapters = array(
      // domain           adapter class
      'newzleech.com' => 'newzleechAdapter',
      'tvbinz.net'    => 'tvBinzAdapter',
      // default adapter class
      0 => 'rssFeedAdapter',
  );

  static protected function getDomain($url)
  {
    return preg_replace('|.*\.([^.]+\.[^.]+)$|', '\1', parse_url($url, PHP_URL_HOST));
  }

  static public function getAdapter($feed) {
    $url = $feed->url;
    $domain = self::getDomain($url);

    if(isset(self::$adapters[$domain]))
      $class = self::$adapters[$domain];
    else
      $class = self::$adapters[0];

    Yii::trace("Initializing $class for $url", 'application.components.feedAdapterRouter');
    $adapter = new $class($feed);

    if($adapter instanceof IFeedAdapter)
      return $adapter;
    else
      throw new CException('Created adapter is not an IFeedAdapter: '.get_class($adapter));
  }
}

