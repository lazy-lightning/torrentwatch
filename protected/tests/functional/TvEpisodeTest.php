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
      'makeFavoriteButton'    => "xpath=id('tvEpisode-1')/div[1]/a[3]",
      'makeFavoriteButton2'   => "xpath=id('tvEpisode-2')/div[1]/a[3]",
      'newFavoriteButton'     => "xpath=id('favoriteTvShow-li-')/a",
      'startDownloadButton'   => "xpath=id('tvEpisode-1')/div[1]/a[2]",
      'tvEpisode'             => 'id=tvEpisode-1',
  );

  // This method is overridden because with active tvEpisodes the welcome
  // screen wont be displayed
  protected function closeWelcome() {
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
