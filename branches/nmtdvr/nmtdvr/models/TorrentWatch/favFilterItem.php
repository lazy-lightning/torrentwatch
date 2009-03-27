<?php
abstract class favFilterItem implements chainItem {
  public function run($favorite, $args) {
    return $this->favFilter($favorite, $args[0]);
  }
}

