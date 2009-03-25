<?
Mock::generatePartial('rss', 'MockRss', array('update'));
Mock::generate('favorite', 'MockFavorite');

class testOfTorrentWatch extends TwUnitTestCase {

  var $tw, $feed, $fav;
  var $itemCount;

  function setUp() {
    parent::setUp();
    $this->tw = new TorrentWatch();
  }

  function tearDown() {
    unset($this->tw->feeds, $this->tw->favorites);
    unset($this->tw, $this->feed, $this->fav);
    parent::tearDown();
  }

  function testInit() {
    // Verify basic Structures were setup
    $this->assertIsA($this->tw->client, 'client', 'Client Initialized');
    $this->assertIsA($this->tw->config, 'TwConfig', 'TwConfig Initialized');
    $this->assertIsA($this->tw->favorites, 'favorites', 'Favorites Initialied');
    $this->assertIsA($this->tw->feeds, 'feeds', 'Feeds Initialized');
    $this->assertIsA($this->tw->history, 'history', 'History Initialized');
  }

}
?>
