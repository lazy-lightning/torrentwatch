<?php

class TvEpisodeTest extends WebTestCase
{
  protected $fixtures = array(
      'tvEpisodes'      => 'tvEpisode',
      'tvShows'         => 'tvShow',
      'feeds'           => 'feed',
      'feedItems'       => 'feedItem',
      'favoriteTvShows' => 'favoriteTvShow',
  );

  public $locators = array(
      'closeDialog'           => "css=div.close",
      'createdFavoriteButton' => "xpath=id('favoriteTvShow-li-1')/a",
      'createdFavoriteButton2'=> "xpath=id('favoriteTvShow-li-2')/a",
      'errorSummary'          => "css=.errorSummary",
      'expose'                => "css=div.expose",
      'favoriteForm'          => "id=favoriteTvShow-1",
      'favoriteForm2'         => "id=favoriteTvShow-2",
      'favoriteTvShowList'    => "id=favoriteTvShowList",
      'makeFavoriteButton'    => "xpath=id('tvEpisode-1')/div[1]/a[2]",
      'makeFavoriteButton2'   => "xpath=id('tvEpisode-2')/div[1]/a[2]",
      'newFavoriteButton'     => "xpath=id('favoriteTvShow-li-')/a",
      'startDownloadButton'   => "xpath=id('tvEpisode-1')/div[1]/a[1]",
      'tvEpisode'             => 'id=tvEpisode-1',
  );

  // This method is overridden because with active tvEpisodes the welcome
  // screen wont be displayed
  protected function closeWelcome() {
  }

  public function setUp()
  {
    $this->getFixtureManager()->setSubFixture('ItemTests');
    parent::setUp();
  }

  public function testMakeFavorite()
  {
    $l = $this->locators; // shorthand access
    $this->waitForElementPresent($l['tvEpisode']);
    // click the make favorite button
    $this->click($l['makeFavoriteButton']);
    // Wait for a form containing the favorite to be displayed
    $this->waitForElementPresentAndVisible($l['favoriteForm']);
    // Verify no errors
    $this->assertElementNotPresent($l['errorSummary']);
    // Check the database for our favorite
    $fav = favoriteTvShow::model()->findAll();
    $this->assertEquals(count($fav), 1);
    $fav = reset($fav);
    // Get the show it should be pointing to
    $tvShow = $this->tvShows('sample');
    // Check that the tv shows name is displayed in the form
    $this->assertText($l['favoriteForm'], $tvShow->title);
    // Check that the tv shows name is displayed in the links
    $this->assertText($l['favoriteTvShowList'], $tvShow->title);
    // Click the link to new favorite, make sure our form goes away
    $this->click($l['newFavoriteButton']);
    $this->waitForElementNotVisible($l['favoriteForm']);
    usleep(600000);
    $this->click($l['createdFavoriteButton']);
    $this->waitForElementVisible($l['favoriteForm']);
    // close dialog window
    $this->click($l['closeDialog']);
    // Click the next make favorite button
    $this->waitForElementNotVisible($l['expose']);
    $this->click($l['makeFavoriteButton2']);
    // Make sure another favorite pops up
    $this->waitForElementPresentAndVisible($l['favoriteForm2']);
    // Make sure there is a button to select this favorite
    $this->assertElementVisible($l['createdFavoriteButton2']);
  }
}
