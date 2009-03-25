<?php

abstract class cacheItem extends arrayItem {
  protected $changed = False;

  function __construct($options) {
    parent::__construct($options);
  }

  function __get($name) {
    return parent::__get($name);
  }

  // Mark changed here as well, for case when custom setter is used
  // and it doesn't fall through to update();
  function __set($name, $value) {
    $this->changed = True;
    return parent::__set($name, $value);
  }

  function __sleep() {
    return parent::__sleep();
  }

  function __wakeup() {
    $this->changed = False;
  }

  function setId($id) {
    if(parent::setId($id)) {
      $this->changed = True;
      return True;
    }
    return False;
  }

  function update($options) {
    $this->changed = True;
    return parent::update($options);
  }

}

