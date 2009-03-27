<?php
class feedFilter extends favFilterItem {
  static public function favFilter($favorite, $feedItem) {
    return ($favorite->feed === $feedItem->feedId || $favorite->feed === '');
  }
}

