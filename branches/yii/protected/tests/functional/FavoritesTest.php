<?php
class FavoritesTest extends WebTestCase
{
  public $fixtures = array(
      'favoriteTvShow'=>'favoriteTvShow',
      'favoriteMovie'=>'favoriteMovie',
      'favoriteString'=>'favoriteString',
      'tvEpisode'=>'tvEpisode',
  );

  public function assertPreConditions()
  {
    parent::assertPreConditions();
    $this->click('link=Favorites');
    $this->waitForElementVisible('id=favorites');
  }

  public function testTvShowFavorite()
  {
    // tvShow will be open be default
    $this->realTest('favoriteTvShow', 'tvShow_id');
  }

  public function testMovieFavorite()
  {
    // Click the movies anchor
    $this->click("xpath=id('favorites')/div[2]/ul/li[2]/a");
    usleep(500000);
    $this->waitForElementPresentAndVisible('id=favoriteMovie_container');
    $this->realTest('favoriteMovie', 'name');
  }

  function testStringFavorite()
  {
    // Click the strings anchor
    $this->click("xpath=id('favorites')/div[2]/ul/li[3]/a");
    usleep(500000);
    $this->waitForElementPresentAndVisible('id=favoriteString_container');
    $this->realTest('favoriteString', 'name');
  }

	public function realTest($type, $idAttr)
	{
    // shorthand for accessing first created favorite via xpath
    $xpath = "xpath=id('$type-1')/";
    // shorthand for accessing the ul of links
    // Verify there is a link to make a new favorite
    $this->assertElementPresent('link=New Favorite');
    // Click the new favorite link and wait for form to load
    $this->click("xpath=id('$type-li-')/a");
    $this->waitForElementPresentAndVisible('css=form.favinfo');
    // Enter a name and click create
    $this->type("name={$type}[{$idAttr}]",'foobar');
    // favoriteString also requires a filter
    if($type === 'favoriteString') 
      $this->type("name={$type}[filter]", 'bazzab');
    $this->clickAndWaitFor('link=Create', "id=$type-1");
    // Make sure our name got to this page
    $this->assertElementNotPresent('css=div.errorSummary');
    $this->assertTextPresent('foobar');
    // try a directory that cant be saved to
    $this->type("css=#{$type}-1 .favorite_savein input", '/etc');
    $this->click($xpath.'div/a[1]');
    // verify an error was presented
    $this->waitForElementPresentAndVisible('css=div.errorSummary');
    $this->assertTextPresent('glob:is not*writable*');
    // Try again with a directory that can save
    $this->type("css=#{$type}-1 .favorite_savein input", '/tmp');
    // And change the qualitys arround
    $this->select($xpath."div[@class='favorite_quality']/select[1]",'value=3');
    $this->select($xpath."div[@class='favorite_quality']/select[2]",'value=5');
    $this->click($xpath.'div/a[1]');
    $this->waitForElementNotPresent('css=div.errorSummary');
    // Verify our change saved
    $this->assertValue($xpath."div/input[@name='{$type}[saveIn]']", '/tmp');
    $this->assertValue($xpath."div[@class='favorite_quality']/select[1]", '3');
    $this->assertValue($xpath."div[@class='favorite_quality']/select[2]", '5');
    // Remove one of the qualitys
    $this->select($xpath."div[@class='favorite_quality']/select[2]", 'value=-1');
    // Click update button
    $this->clickAndWaitFor($xpath.'div/a[1]');
    // Verify it saved
    $this->assertElementNotPresent('css=div.errorSummary');
    $this->assertValue($xpath."div[@class='favorite_quality']/select[2]", '-1');
    // Delete it
    $this->assertElementPresent('link=Delete');
    $this->click('link=Delete');
    // Wait for the response dialog
    $this->waitForElementPresentAndVisible('css=#actionResponse');
    $this->waitForText('css=#actionResponse .dialog_heading', 'Delete Favorite');
    // See if we got a successfull response
    $this->assertTextPresent('successful');
    // Make sure there isn't a link to view our deleted favorite still hanging arround
    $this->assertElementNotPresent('link=foobar');
    // Make sure the form was removed from the page
    $this->assertElementNotPresent($xpath."div");
	}

  public function clickAndWaitFor($locator, $waitFor = 'id=favorites')
  {
    parent::clickAndWaitFor($locator, $waitFor);
  }

}
