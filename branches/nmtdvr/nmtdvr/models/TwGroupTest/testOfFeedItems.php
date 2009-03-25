<?php
class testOfFeedItems extends TwUnitTestCase {
  var $callbackCount;

  function setUp() {
    parent::setUp();
    $this->feedItems = new feedItems('unittest');
    $this->callbackCount = 0;
  }

}
