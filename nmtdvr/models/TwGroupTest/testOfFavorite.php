<?

Mock::generate('rss', 'MockFeed');
Mock::generate('feedItem', 'MockFeedItem');

class testOfFavorite extends TwUnitTestCase {

  var $fav;

  function setUp() {
    parent::setUp();
    $this->fav = new favorite(array('name' => 'name', 'filter' => 'Some.File'));
  }

  function tearDown() {
    unset($this->fav);
    parent::tearDown();
  }

  function testIsMatching() {
    $config = TwConfig::getInstance();
    $feedUrl = 'http://some/feed.php';
    $feed = new MockFeed($this);
    $feed->url = $feedUrl;
    $feedItem = new MockFeedItem($this);
    $feedItem->title = 'Just.Some.File.S03E14.WS.720p.HDTV-XXxxXX';
    $feedItem->episode = 14;
    $feedItem->season = 3;
    foreach(array('simple', 'glob', 'regexp') as $matchStyle) {
      $config->matchStyle = $matchStyle;
  
      $fav = new Favorite(array('name' => 'name', 'filter' => 'Some.File',
                                'feed' => $feedUrl));
      $this->assertTrue($fav->isMatching($feedItem, $feed->url), "$matchStyle: Standard Match");
  
      $feed2 = new MockFeed($this);
      $feed2->url = 'http://other/place.html';
      $this->assertFalse($fav->isMatching($feedItem, 'http://www.google.com'), $matchStyle.': Exclude by Feed');

      $fav = new Favorite(array('name' => 'name', 'filter' => 'Some.File', 'not' => 'foobar'));
      $this->assertTrue($fav->isMatching($feedItem, $feed->url), $matchStyle.': Include by Not');
  
      $fav = new Favorite(array('name' => 'name', 'filter' => 'Some.File', 'not' => '720p'));
      $this->assertFalse($fav->isMatching($feedItem, $feed->url), $matchStyle.': Exclude by Not');
  
      $fav = new Favorite(array('name' => 'name', 'filter' => 'Some.File', 'quality' => '720p'));
      $this->assertTrue($fav->isMatching($feedItem, $feed->url), $matchStyle.': Include by Quality');
 
      $fav = new Favorite(array('name' => 'name', 'filter' => 'Some.File', 'quality' => '1080p'));
      $this->assertFalse($fav->isMatching($feedItem, $feed->url), $matchStyle.': Exclude by Quality');
 
      $fav = new Favorite(array('name' => 'name', 'filter' =>  'Some.File', 'episodes' => '3x10-19]'));
      $this->assertTrue($fav->isMatching($feedItem, $feed->url), $matchStyle.': Include by Episode');
  
      $fav = new Favorite(array('name' => 'name', 'filter' => 'Some.File', 'episodes' => '3x1-9'));
      $this->assertFalse($fav->isMatching($feedItem, $feed->url), $matchStyle.': Exclude by Episode');
    }
    $config->matchStyle = $matchStyle = 'simple';
    $fav = new Favorite(array('name' => 'name', 'filter' => 'Some.File', 'not' => '720p 1080p'));
    $this->assertFalse($fav->isMatching($feedItem, $feed->url), $matchStyle.': Multiple not filters');

    $fav = new Favorite(array('name' => 'name', 'filter' => 'Some.File', 'quality' => '1080p 720p'));
    $this->assertTrue($fav->isMatching($feedItem, $feed->url), $matchStyle.': Multiple quality filters');

    $config->matchStyle = $matchStyle = 'glob';
    $fav = new Favorite(array('name' => 'name', 'filter' => 'So*le'));
    $this->assertTrue($fav->isMatching($feedItem, $feed->url), $matchStyle.': Accept glob filter');

    $fav = new Favorite(array('name' => 'name', 'filter' => 'Some.File', 'not' => 'So*le'));
    $this->assertFalse($fav->isMatching($feedItem, $feed->url), $matchStyle.': Accept glob not');

    $config->matchStyle = $matchStyle = 'regexp';
    $fav = new Favorite(array('name' => 'name', 'filter' => 'Some.(Foo|Bar|File)'));
    $this->assertTrue($fav->isMatching($feedItem, $feed->url), $matchStyle.': Accept regexp filter');

    $fav = new Favorite(array('name' => 'name', 'filter' => 'Some', 'not' => '(Foo|Bar|File)'));
    $this->assertFalse($fav->isMatching($feedItem, $feed->url), $matchStyle.': Accept regexp not');
  }

  function testMyStrpos() {
    $hay = 'Another 123 of Life';
    $this->assertTrue($this->fav->my_strpos($hay, '123'), 'single needle');
    $this->assertTrue($this->fav->my_strpos($hay, '123 Foo'), 'multiple needles');
    $this->assertFalse($this->fav->my_strpos($hay, 'Foo'), 'bad needle');
  }

  function testIsNewEpisode() {
    $a = new MockFeedItem($this);
    $a->season = $a->episode = 5;
    $this->fav->updateRecent($a);
    $this->assertTrue($this->fav->isNewEpisode(5, 6), 'Same season, higher episode num');
    $this->assertTrue($this->fav->isNewEpisode(6, 4), 'higher season, lower episode num');
    $this->assertFalse($this->fav->isNewEpisode(5, 5), 'Same season, same episode num');
    $this->assertFalse($this->fav->isNewEpisode(4, 6), 'Lower season, higher episode num');
    $a->season = 7;
    $this->fav->updateRecent($a);
    $this->assertFalse($this->fav->isNewEpisode(7, 4), 'update and test Same season, lower episode');
  } 

  function testUpdateRecent() {
    $a = new MockFeedItem($this);
    $a->episode = $a->season = 5;
    $b = clone $a;
    $this->fav->updateRecent($a);
    // Actions to $a should affect the stored information
    // Actions to $b should not
    $commands = array('$b->episode--;',
                      '$a->episode++;',
                      '$b->season--;',
                      '$a->season++;');
    foreach($commands as $key => $command) {
      eval($command);
      $result = eval('return '.substr($command,0,2).';');
      $this->fav->updateRecent($result);
      $this->assertEqual($this->fav->recentSeason, $a->season, 'Season: %s');
      $this->assertEqual($this->fav->recentEpisode,$a->episode, 'Episode: %s');
      unset($b);
      $b = clone $a;
    }
  }
}
