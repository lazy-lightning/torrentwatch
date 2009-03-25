<?php
Mock::generate('lastRSS');
Mock::generatePartial('rss', 'MockRssAdd', array('addFeedItem', 'needsUpdate'));
Mock::generatePartial('rss', 'MockRssCallback', array());

class testOfRssFeed extends TwUnitTestCase {

  var $lastRss;
  var $feed;
  var $sampleUrl = 'http://test/url.html';

  function setUp() {
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
  }

}
?>
