<?php
abstract class favFilterItem implements chainItem {
  public function run($favorite, $args) {
    list($feedItem, $feedId) = $args;
    return $this->favFilter($favorite, $feedItem, $feedId);
  }
}

