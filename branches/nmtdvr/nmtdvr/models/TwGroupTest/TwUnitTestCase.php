<?php
Mock::generatePartial('feed', 'MockFeed', array('updateReal'));

class TwUnitTestCase extends UnitTestCase {

  public static $sampleTime = Null;
  var $url;

  function setUp() {
    DataCache::setPrefix('tw_unittest_');
  }

  function tearDown() {
    @exec("rm -f /dev/shm/tw_unittest_* 2>&1 /dev/null");
    unset($this->mockFeed, $this->url, $this->feedItem, $this->feed, $this->favorite);
  }

  function setUpFavorite($options = array()) {
    if(empty($options['name']))
      $options['name'] = $this->generateSampleTitle();

    return new favorite($options);
    
  }

  function setUpFeedItem($rssItem = '') {
     if(empty($rssItem)) {
       if(empty($this->rssItem)) 
         $this->rssItem = $this->generateSampleRssItem();
       $rssItem = $this->rssItem;
     }
     return new feedItem($rssItem);
  }

  function setUpMockFeed($url = '') {
    if(empty($url)) {
      if(empty($this->url))
        $this->url = $this->generateSampleLink();
      $url = $this->url;
    }

    $feed = new MockFeed();
    $feed->setReturnValue('updateReal', True);
    // is this valid, or does it grab the mock constructor?
    $feed->__construct(array('url' => $this->url));
    return $feed;
  }

  function generateSampleLink() {
    return 'http://www.sample.com/'.rand(0,2147483647).'/'.rand(0,2147483647).'/testpage.html';
  }

  function generateSampleTitle($sep = ' ') {
    $noun = array("Dream","Dreamer","Dreams","Waves", "Sword","Kiss","Sex","Lover", 
                  "Slave","Slaves","Pleasure","Servant", "Servants","Snake","Soul","Touch", 
                  "Men","Women","Gift","Scent", "Ice","Snow","Night","Silk","Secret","Secrets", 
                  "Game","Fire","Flame","Flames", "Husband","Wife","Man","Woman","Boy","Girl", 
                  "Truth","Edge","Boyfriend","Girlfriend", "Body","Captive","Male","Wave","Predator", 
                  "Female","Healer","Trainer","Teacher", "Hunter","Obsession","Hustler","Consort", 
                  "Dream", "Dreamer", "Dreams","Rainbow", "Dreaming","Flight","Flying","Soaring", 
                  "Wings","Mist","Sky","Wind", "Winter","Misty","River","Door", 
                  "Gate","Cloud","Fairy","Dragon", "End","Blade","Beginning","Tale", 
                  "Tales","Emperor","Prince","Princess", "Willow","Birch","Petals","Destiny", 
                  "Theft","Thief","Legend","Prophecy", "Spark","Sparks","Stream","Streams","Waves", 
                  "Sword","Darkness","Swords","Silence","Butterfly","Shadow","Ring","Rings","Emerald",
                  "Storm","Storms","Mists","World","Worlds", "Alien","Lord","Lords","Ship","Ships","Star", 
                  "Stars","Force","Visions","Vision","Magic", "Wizards","Wizard","Heart","Heat","Twins", 
                  "Twilight","Moon","Moons","Planet","Shores", "Pirates","Courage","Time","Academy", 
                  "School","Rose","Roses","Stone","Stones", "Sorcerer","Shard","Shards","Slave","Slaves", 
                  "Servant","Servants","Serpent","Serpents", "Snake","Soul","Souls","Savior","Spirit", 
                  "Spirits","Voyage","Voyagers", "Return","Legacy","Birth","Healer","Healing", 
                  "Year","Years","Death","Dying","Luck","Elves", "Touch","Son","Sons","Child","Children", 
                  "Illusion","Sliver","Destruction", "Gift","Word","Words","Thought","Thoughts","Scent", 
                  "Ice","Snow","Night","Silk","Guardian", "Angels","Secret","Secrets","Search","Eye","Eyes", 
                  "Danger");/*,"Game","Fire","Flame","Flames","Bride", ,"Wife","Time","Flower","Flowers", 
                  "Light","Lights","Door","Doors","Window","Windows", "Bridge","Ashes","Memory","Thorn", 
                  "Thorns","Name","Names","Future","Past", "History","Something","Nothing","Someone", 
                  "Nobody","Person","Man","Woman","Boy","Girl", "Way","Mage","Witch","Witches","Lover", 
                  "Tower","Valley","Abyss","Hunter", "Truth","Edge" ); */
    $adj  = array("Lost","Only","Last","First", "Third","Sacred","Bold","Lovely", 
                  "Final","Missing","Shadowy","Seventh", "Dwindling","Missing","Absent", 
                  "Vacant","Cold","Hot","Burning","Forgotten", "Weeping","Dying","Lonely","Silent", 
                  "Laughing","Whispering","Forgotten","Smooth", "Silken","Rough","Frozen","Wild", 
                  "Trembling","Fallen","Ragged","Broken", "Cracked","Splintered","Slithering","Silky", 
                  "Wet","Magnificent","Luscious","Swollen", "Erect","Bare","Naked","Stripped", 
                  "Captured","Stolen","Sucking","Licking", "Growing","Kissing","Green","Red","Blue", 
                  "Azure","Rising","Falling","Elemental", "Bound","Prized","Obsessed","Unwilling", 
                  "Hard","Eager","Ravaged","Sleeping", "Wanton","Professional","Willing","Devoted", 
                  "Misty","Lost","Only","Last","First", "Final","Missing","Shadowy","Seventh", 
                  "Dark","Darkest","Silver","Living", "Black","White","Hidden","Entwined","Invisible", 
                  "Next","Seventh","Red","Green","Blue", "Purple","Grey","Bloody","Emerald","Diamond", 
                  "Frozen","Sharp","Delicious","Dangerous", "Twinkling","Dwindling","Missing","Absent", 
                  "Vacant","Cold","Hot","Burning","Forgotten", "No","All","Every","Each","Which","What", 
                  "Playful","Silent","Weeping","Dying","Silent", "Laughing","Forgotten","Smooth","Silken",
                  "Rough","Frozen","Wild","Fallen", "Ragged","Broken","Cracked","Splintered" );

    $a = rand(0,count($noun)-1);
    $b = rand(0,count($noun)-1);
    $c = rand(0,count($adj)-1);
    switch(rand(0,5)) {
      case '0':
        return($adj[$c].$sep.$noun[$a]);
      case '1':
        return("The$sep".$adj[$c]."$sep".$noun[$a]);
      case '2':
        return($noun[$a].$sep.'of'.$sep.$noun[$b]);
      case '3':
        return("The$sep".$noun[$a]."s$sep".$noun[$b]);
      case '4':
        return("The$sep".$noun[$a].$sep."of".$sep."the".$sep.$noun[$b]);
      case '5':
        return($noun[$a]."${sep}in${sep}the$sep".$noun[$b]);
    }
   
  }
  function generateSampleFeedItemTitle($sep = '.', $season = 0, $episode = 0) {
    if($season == 1) 
      $episodeArray = array('part%2$dof32', "part$sep%2\$d${sep}of${sep}32", '%2$02dof32');
    else
      $episodeArray = array('%dx%d', 'S%02dE%02d');
    shuffle($episodeArray);
    $qualityArray[0] = array('HDTV', 'PDTV', 'DVDRip', 'DSRip', 'HR.PDTV', 'SatRip', 'DVDScr', 'TVRip');
    $qualityArray[1] = array('XviD', 'x264');
    shuffle($qualityArray[0]);
    shuffle($qualityArray[1]);

    $numQuality = rand(1,2);
    $showEpisode = (rand(0,3) < 3);

    $shortTitle = $title = $this->generateSampleTitle($sep);

    if($showEpisode && $season != 0 && $episode != 0)
      $title .= $sep.sprintf($episodeArray[0], $season, $episode);
    else
      $season = $episode = 0;
    for($i=0;$i<$numQuality;$i++)
      $title .= $sep.$qualityArray[$i][0];
    return array($title, $shortTitle, $season, $episode);
  }

  static function generateSampleTime() {
    if(self::$sampleTime == Null)
      self::$sampleTime = time();
    else
      self::$sampleTime -= 100;
    return self::$sampleTime;
  }
    
  function generateSampleRssItem($sep = '.', $season = 0, $episode = 0) {
    list($title, $shortTitle, $season, $episode) = $this->generateSampleFeedItemTitle($sep=='-'?'.':$sep, $season, $episode);
    return array('title' => $title,
                  'link' => $this->generateSampleLink(),
                  'pubDate' => $this->generateSampleTime(), 'description' => 'Sample Description',
                  // Items used to verify matching routines
                  'unittestShortTitle' => $shortTitle, 'unittestSeason' => $season, 'unittestEpisode' => $episode);
  }

  function generateSampleRawRssFeed($itemCount = 5) {
    $sample = array();
    for($i=0;$i<$itemCount;$i++)
      $sample['items'][] = $this->generateSampleRssItem('.', rand(1,5), rand(1,15));
    return $sample;
  }
}
