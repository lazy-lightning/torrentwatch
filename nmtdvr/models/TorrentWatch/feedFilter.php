<?php
class feedFilter extends favFilterItem {
  static public function favFilter($favorite, $feedItem, $feedId) {
    return ($favorite->feed === $feedId || $favorite->feed === '');
  }
}

