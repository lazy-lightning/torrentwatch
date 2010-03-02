<?php

class TvEpisodeTest extends WebTestCase
{
  protected $fixtures = array(
      'tvEpisodes'      => 'tvEpisode',
      'tvShows'         => 'tvShow',
      'feeds'           => 'feed',
      'feedItems'       => 'feedItem',
      'favoriteTvShows' => 'favoriteTvShow',
      'dvrConfig'       => ':dvrConfig',
  );

  public $locators = array(
      'actionResponse'        => "id=actionResponse",
      'closeDialog'           => "css=div.close",
      'errorSummary'          => "css=.errorSummary",
      'expose'                => "css=div.expose",
      'favoriteForm'          => "id=favoriteTvShow-",
      'favoriteNameInput'     => "id=favoriteTvShow_tvShow_id",
      'favoriteTvShowList'    => "id=favoriteTvShowList",
      'feedItem'              => "css=.torrent_feed",
      'feedItemDetails'       => "xpath=id('tv_container')/x:li[1]/x:div[2]",
      'inspect-1'             => "xpath=id('tvEpisode-1')/x:a",
      'inspect-2'             => "xpath=id('tvEpisode-2')/x:a",
      'inspectorContainer'    => "id=inspector_container",
      'inspector-1'           => "id=tvEpisodeDetails-1",
      'inspector-2'           => "id=tvEpisodeDetails-2",
      'makeFavoriteButton'    => "xpath=id('tvEpisode-1')/div[1]/a[3]",
      'makeFavoriteButton2'   => "xpath=id('tvEpisode-2')/div[1]/a[3]",
      'newFavoriteButton'     => "xpath=id('favoriteTvShow-li-')/a",
      'startDownloadButton'   => "xpath=id('tvEpisode-1')/div[1]/a[2]",
      'toggleInspector'       => "css=#inspector a",
      'tvEpisode'             => "xpath=id('tvEpisode-1')/x:div[3]",
  );

  // This method is overridden because with active tvEpisodes the welcome
  // screen wont be displayed
  protected function closeWelcome() {
  }

  public function testGetFeedItems()
  {
    $l = $this->locators;
    $this->waitForElementPresent($l['tvEpisode']);
    $this->click($l['tvEpisode']);
    $this->waitForElementPresent($l['feedItem']);
    $this->assertText($l['feedItemDetails'], $this->feedItems['first']['title']);
  }

  public function testInspector()
  {
    $l = $this->locators;
    $hiddenLeft = $this->getElementPositionLeft($l['inspectorContainer']);
    $this->waitForElementPresent($l['tvEpisode']);
    $this->click($l['inspect-1']);
    $this->waitForElementPresentAndVisible($l['inspector-1']);
    sleep(1);
    $displayLeft = $this->getElementPositionLeft($l['inspectorContainer']);
    $this->assertLessThan($hiddenLeft-300, $displayLeft);
    $this->assertText($l['inspector-1'], $this->tvShows['sample']['description']);
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

  public function testMakeFavorite()
  {
    $l = $this->locators; // shorthand access
    $this->waitForElementPresent($l['tvEpisode']);
    $this->click($l['makeFavoriteButton']);
    // Wait for a form containing the favorite to be displayed
    $this->waitForElementPresentAndVisible($l['favoriteForm']);
    // Get the show it should be pointing to
    $tvShow = $this->tvShows('sample');
    // Check that the tv shows name is displayed in the form
    $this->assertValue($l['favoriteNameInput'], $tvShow->title);
    // close dialog window
    $this->click($l['closeDialog']);
    // Click the next make favorite button
    $this->waitForElementNotVisible($l['expose']);
    $this->click($l['makeFavoriteButton2']);
    // Make sure our window opens back up
    $this->waitForElementPresentAndVisible($l['favoriteForm']);
    // get the show it should be pointing to
    $tvShow = $this->tvShows('sampletwo');
    $this->assertValue($l['favoriteNameInput'], $tvShow->title);
  }

  public function testStartDownload()
  {
    $l = $this->locators; // shorthand access
    $this->waitForElementPresent($l['tvEpisode']);
    // click the start download button
    $this->click($l['startDownloadButton']);
    // wait for response dialog
    $this->waitForElementPresentAndVisible($l['actionResponse']);
    // verify no errors
    $this->assertElementNotPresent($l['errorSummary']);
    // check for success string
    $this->assertText($l['actionResponse'], 'Started');
  }

}
