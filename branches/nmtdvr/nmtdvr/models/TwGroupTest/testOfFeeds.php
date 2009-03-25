<?php

class testOfFeeds extends TwUnitTestCase {
  function setUp() {
    parent::setUp();
    $this->feeds = new feeds();
  }
  
  function tearDown() {
    unset($this->feeds);
    parent::tearDown();
  }
}
