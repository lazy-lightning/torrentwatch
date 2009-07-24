<?php

// This is a simple class for choosing between various available feedAdapters
// to add a new adapter just insert the required information into the adapters array

class feedAdapterRouter {

  static protected $adapters = array(
      // Class name                url regexp
      'feedAdapterNewzleech'  => '/newzleech.com/i',
      'feedAdapterTvBinz'     => '/tvbinz.net/i',
  );

  static public function getAdapter($feed) {
    $url = $feed->url;
    foreach(self::$adapters as $class => $reg) {
      if(preg_match($reg, $url)) {
        Yii::log("Initializing $class for $url");
        return new $class($feed);
      }
    }
    $x = new feedAdapter($feed);
    return $x; 
  }
}

