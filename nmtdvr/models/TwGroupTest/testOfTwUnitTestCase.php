<?

class testOfTwUnitTestCase extends UnitTestCase {

  var $twTestCase;

  function setUp() {
    $this->twTestCase = new TwUnitTestCase();
  }

  function tearDown() {
  }

  function isValidItem($item) {
    return (isset($item['title']) && isset($item['link']) &&
            isset($item['pubDate']) && isset($item['description']));
  }

  function testGenerateSampleLink() {
   $this->assertPattern('/http:\/\/.*/', $this->twTestCase->generateSampleLink());
   $this->assertNotEqual($this->twTestCase->generateSampleLink(), $this->twTestCase->generateSampleLink());
  }

  function testGenerateSampleFeedItemTitle() {
    list($title, $shortTitle) = $this->twTestCase->generateSampleFeedItemTitle();
    $this->assertPattern("/^$shortTitle.+/", $title);
    $this->assertTrue(strlen($title) >= 10, 'Title at least 10 chars');
  }

  function testGenerateSampleRssItem() {
    $item = $this->twTestCase->generateSampleRssItem();
    $this->assertTrue($this->isValidItem($item), 'valid item');
    $otheritem = $this->twTestCase->generateSampleRssItem();
    $this->assertTrue($item['pubDate'] > $otheritem['pubDate'], 'Second item older than first');
  }

  function testGenerateSampleRawRssFeed() {
    $numItems = 3;
    $itemCount = 0;
    $feed = $this->twTestCase->generateSampleRawRssFeed($numItems);
    $this->assertEqual(count($feed['items']), $numItems);
    foreach($feed['items'] as $item)
      $itemCount += (int)$this->isValidItem($item);
    $this->assertEqual($numItems, $itemCount);
  }
  
}

