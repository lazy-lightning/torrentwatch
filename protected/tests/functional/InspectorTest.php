<?php

class InspectorTest extends WebTestCase
{
  public $fixtures = array(
      'dvrConfig'       => ':dvrConfig',
      'feeds'           => 'feed',
      'feedItems'       => 'feedItem',
      'favoriteTvShows' => 'favoriteTvShow',
      'movies'          => 'movie',
      'tvEpisodes'      => 'tvEpisode',
      'tvShows'         => 'tvShow',
  );

  public $locators = array(
      'closeDialog'           => "css=div.close",
      'expose'                => "css=div.expose",
      'feedItem'              => "id=feedItem-1",
      'feedItem-3'            => "id=feedItem-3",
      'inspect-1'             => "xpath=id('{type}-1')/x:a",
      'inspect-2'             => "xpath=id('{type}-2')/x:a",
      'inspectorContainer'    => "id=inspector_container",
      'inspector-1'           => "id={media}Details-1",
      'inspector-2'           => "id={media}Details-2",
      "movieItem-1"           => "xpath=id('movie-1')/x:div[2]",
      "movieItem-2"           => "xpath=id('movie-2')/x:div[2]",
      'movieTab'              => "xpath=id('feedItems_container')/x:ul/x:li[2]/x:a/x:span",
      'toggleInspector'       => "css=#inspector a",
      'movieItem'             => "xpath=id('movie-1')/x:div[2]",
      'tvItem'                => "xpath=id('tvEpisode-1')/x:div[3]",
      'tvItem-2'              => "xpath=id('tvEpisode-2')/x:div[3]",
  );

  // This method is overridden because with active tvEpisodes the welcome
  // screen wont be displayed
  protected function closeWelcome() {
  }


  /* Cant be in provider, needs to click things on page */
  public function testInspectorMovieFeedItem()
  {
    $l=$this->locators;
    $l['inspect-1']="xpath=id('{type}-3')/x:a";
    $l['inspect-2']="xpath=id('{type}-4')/x:a";
    foreach($l as $key => $locator)
      $l[$key] = str_replace(array('{media}', '{type}'), array('movie', 'feedItem'), $locator);

    $this->click($l['movieTab']);

    $this->waitForElementPresentAndVisible($l['movieItem-2']);
    $this->click($l['movieItem-1']);
    $this->click($l['movieItem-2']);

    $this->testInspectorReal($l, $this->movies['sample']['plot'], 'feedItem-3');
  }

  /* Cant be in provider, needs to click things on page */
  public function testInspectorTvFeedItem()
  {
    $this->click($this->locators['tvItem-2']);
    $this->testInspector('feedItem', 'tvEpisode', 'tvItem', 'feedItem');
  }

  public function provider() {
   
    return array(
        //        used in locators      locators used to set up test
        //      itemType   mediaType  |  click       waitfor
        array('tvEpisode','tvEpisode',  false,      'tvItem'),
        array('movie',    'movie',      'movieTab', 'movieItem'),
    );
  }
  /**
   * testInspector 
   * 
   * @dataProvider provider
   * @param mixed $locators 
   * @return void
   */
  public function testInspector($type, $media, $clickMe, $mediaItem)
  {
    // cant be done in provider for some reason
    if($clickMe==='movieTab') 
      $description = $this->movies['sample']['plot'];
    else
      $description = $this->tvShows['sample']['description'];

    foreach($this->locators as $key => $locator)
      $l[$key] = str_replace(array('{media}', '{type}'), array($media, $type), $locator);

    if($clickMe)
      $this->click($l[$clickMe]);

    $this->testInspectorReal($l, $description, $mediaItem);
  }

  protected function testInspectorReal($l, $description, $mediaItem)
  {
    $hiddenLeft = $this->getElementPositionLeft($l['inspectorContainer']);
    $this->waitForElementPresent($l[$mediaItem]);
    $this->click($l['inspect-1']);
    $this->waitForElementPresentAndVisible($l['inspector-1']);
    sleep(1);
    $displayLeft = $this->getElementPositionLeft($l['inspectorContainer']);
    $this->assertLessThan($hiddenLeft-300, $displayLeft);
    $this->assertText($l['inspector-1'], $description);
    $this->click($l['toggleInspector']);
    sleep(1);
    $this->assertElementPositionLeft($l['inspectorContainer'], $hiddenLeft);
    $this->click($l['toggleInspector']);
    sleep(1);
    $this->assertElementPositionLeft($l['inspectorContainer'], $displayLeft);
    $this->click($l['inspect-2']);
    $this->waitForElementPresentAndVisible($l['inspector-2']);
    $this->assertElementNotPresent($l['inspector-1']);
  }

}
