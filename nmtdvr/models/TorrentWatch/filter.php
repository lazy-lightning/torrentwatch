<?php
abstract class filter extends cacheItem {
  // contains a filter chain for each implementing class
  static private $filters = array();

  // must return a chain object which contains the neccessary filters
  abstract static protected function buildFilter();

  // $obj is always passed as $this in the calling class
  protected function runFilter($args) {
    $class = get_class($this);

    if(!isset(self::$filters[$class])) {
      self::$filters[$class] = $this->buildFilter();
    }

    return self::$filters[$class]->run($this, $args);
  }

}
