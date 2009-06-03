<?php

class feedAdapterRouter {

  static protected $adapters = array(
      'feedAdapterNewzleech'  => '/newzleech.com/i',
  );

  static public function getAdapter($feed) {
    $url = $feed->url;
    foreach(self::$adapters as $class => $reg) {
      if(preg_match($reg, $url)) {
        Yii::log("Initializing $class for $url", CLogger::LEVEL_ERROR);
        return new $class($feed);
      }
    }
    return new feedAdapter($feed);
  }
}

