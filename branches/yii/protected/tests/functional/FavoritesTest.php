<?php
class FavoritesTest extends WebTestCase
{
  protected $fixtures = array(
      'favoriteTvShow'=>'favoriteTvShow',
      'favoriteMovie'=>'favoriteMovie',
      'favoriteString'=>'favoriteString',
      'tvEpisode'=>'tvEpisode',
  );

  protected $locators = array(
      'actionResponseDialog' => 'id=actionResponse',
      'actionResponseHeading' => 'css=#actionResponse .dialog_heading',
      'createFavoriteButton' => 'link=Create',
      'deleteFavoriteButton' => 'link=Delete',
      'errorSummary' => 'css=.errorSummary',
      'favoriteFilterInput' => 'name={type}[filter]',
      'favoriteMovieContainer' => 'id=favoriteMovie_container',
      'favoriteNameInput' => "name={type}[{id}]",
      'favoriteSaveInInput' => "css=#{type}-1 .favorite_savein input",
      'favoriteStringContainer' => 'id=favoriteString_container',
      'favoriteQualitySelect' => "{}div[@class='favorite_quality']/select",
      'favoritesDialog' => 'id=favorites',
      'favoriteForm' => '{}div',
      'newFavoriteLink' => "xpath=id('{type}-li-')/a",
      'saveFavoriteButton' => '{}div/a[1]',
      'toggleFavoriteMovieTab' => "xpath=id('favorites')/div[2]/ul/li[2]/a",
      'toggleFavoriteStringTab' => "xpath=id('favorites')/div[2]/ul/li[3]/a",
      'toggleFavoritesButton' => 'link=Favorites',
      'updateFavoriteButton' => 'link=Update',
  );

  public function assertPreConditions()
  {
    $l = $this->locators;
    parent::assertPreConditions();
    $this->click($l['toggleFavoritesButton']);
    $this->waitForElementVisible($l['favoritesDialog']);
  }

  // used for accessing different favorites without confusing the similar forms
  protected function getLocators($type,$id) {
    $xpath = "xpath=id('$type-1')/";
    $out = array();
    foreach($this->locators as $key =>$locator)
        $out[$key] = str_replace(array('{}','{type}','{id}'), array($xpath,$type,$id), $locator);
    return $out;
  }

  public function testTvShowFavorite()
  {
    // tvShow will be open be default
    $this->realTest($this->getLocators('favoriteTvShow', 'tvShow_id'));
  }

  public function testMovieFavorite()
  {
    $l = $this->getLocators('favoriteMovie', 'name');
    // Click the movies anchor
    $this->click($l['toggleFavoriteMovieTab']);
    usleep(500000);
    $this->waitForElementPresentAndVisible($l['favoriteMovieContainer']);
    $this->realTest($l);
  }

  function testStringFavorite()
  {
    $l = $this->getLocators('favoriteString', 'name');
    // Click the strings anchor
    $this->click($l['toggleFavoriteStringTab']);
    usleep(500000);
    $this->waitForElementPresentAndVisible($l['favoriteStringContainer']);
    $this->realTest($l);
  }

	public function realTest($locators)
	{
    $l = $locators;
    $this->assertElementPresent($l['newFavoriteLink']);
    // Click the new favorite link and wait for form to load
    $this->click($l['newFavoriteLink']);
    $this->waitForElementPresentAndVisible($l['createFavoriteButton']);
    // Enter a name and click create
    $this->type($l['favoriteNameInput'],'foobar');
    // favoriteString also requires a filter
    if($this->isElementPresent($l['favoriteFilterInput']))
      $this->type($l['favoriteFilterInput'], 'bazzab');
    $this->clickAndWaitFor($l['createFavoriteButton'], $l['updateFavoriteButton'],false);
    // Make sure our name got to this page
    $this->assertElementNotPresent($l['errorSummary']);
    $this->assertTextPresent('foobar');
    // try a directory that cant be saved to
    $this->type($l['favoriteSaveInInput'], '/etc');
    $this->click($l['updateFavoriteButton']);
    // verify an error was presented
    $this->waitForElementPresentAndVisible($l['errorSummary']);
    $this->assertTextPresent('glob:is not*writable*');
    // Try again with a directory that can save
    $this->type($l['favoriteSaveInInput'], '/tmp');
    // And change the qualitys arround
    $this->select($l['favoriteQualitySelect']."[1]",'value=3');
    $this->select($l['favoriteQualitySelect']."[2]",'value=5');
    $this->click($l['updateFavoriteButton']);
    $this->waitForElementNotPresent($l['errorSummary']);
    // Verify our change saved
    $this->assertValue($l['favoriteSaveInInput'], '/tmp');
    $this->assertValue($l['favoriteQualitySelect']."[1]", '3');
    $this->assertValue($l['favoriteQualitySelect']."[2]", '5');
    // Remove one of the qualitys
    $this->select($l['favoriteQualitySelect']."[2]", 'value=-1');
    // Click update button
    $this->clickAndWaitFor($l['updateFavoriteButton']);
    // Verify it saved
    $this->assertElementNotPresent($l['errorSummary']);
    $this->assertValue($l['favoriteQualitySelect']."[2]", '-1');
    // Delete it
    $this->assertElementPresent($l['deleteFavoriteButton']);
    $this->click($l['deleteFavoriteButton']);
    // Wait for the response dialog
    $this->waitForElementPresentAndVisible($l['actionResponseDialog']);
    $this->waitForText($l['actionResponseHeading'], 'Delete Favorite');
    // See if we got a successfull response
    $this->assertTextPresent('successful');
    // Make sure there isn't a link to view our deleted favorite still hanging arround
    $this->assertElementNotPresent('link=foobar');
    // Make sure the form was removed from the page
    $this->assertElementNotPresent($l['favoriteForm']);
	}

  public function clickAndWaitFor($locator, $waitFor = 'id=favorites', $mid='id=progressbar')
  {
    parent::clickAndWaitFor($locator, $waitFor, $mid);
  }

}
