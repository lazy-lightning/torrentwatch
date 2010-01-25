<?php
class FavoriteTvShowTest extends WebTestCase
{
  public $fixtures = array(
      'favoriteTvShow'=>'favoriteTvShow',
  );

	public function testCRUDFavorite()
	{
    // Click the favorites button, tvShow will be open
    // by default
    $this->click('link=Favorites');
    $this->waitForElementVisible('id=favorites');
    // Verify the tv shows favorites are default on open
    $this->assertElementVisible('id=favoriteTvShows');
    // Verify there is a link to make a new favorite
    $this->assertElementPresent('link=New Favorite');
    // Click the link and wait for form to load
    $this->click('link=New Favorite');
    $this->waitForElementPresentAndVisible('css=form.favinfo');
    // Enter a tv show name and click create
    $this->assertElementPresent('name=favoriteTvShow[tvShow_id]');
    $this->type('name=favoriteTvShow[tvShow_id]','foobar');
    $this->clickAndWaitFor('link=Create');
    // Make sure our name got to this page
    $this->assertElementNotPresent('css=div.errorSummary');
    $this->assertTextPresent('foobar');
    // try a directory that cant be saved to
    $this->type('name=favoriteTvShow[saveIn]', '/etc');
    $this->clickAndWaitFor('link=Update');
    // verify an error was presented
    $this->assertElementPresent('css=div.errorSummary');
    $this->assertTextPresent('glob:is not*writable*');
    // Try again with a directory that can save
    $this->type('name=favoriteTvShow[saveIn]', '/tmp');
    // And change the qualitys arround
    $this->select('id=quality_id_1','value=3');
    $this->select('id=quality_id_2', 'value=5');
    $this->clickAndWaitFor('link=Update');
    // Verify our change saved
    $this->assertElementNotPresent('css=div.errorSummary');
    $this->assertValue('name=favoriteTvShow[saveIn]', '/tmp');
    $this->assertValue('id=quality_id_1', '3');
    $this->assertValue('id=quality_id_2', '5');
    // Remove one of the qualitys
    $this->select('id=quality_id_2', 'value=-1');
    $this->clickAndWaitFor('link=Update');
    // Verify it saved
    $this->assertElementNotPresent('css=div.errorSummary');
    $this->assertValue('id=quality_id_2', '-1');
    // Delete it
    $this->assertElementPresent('link=Delete');
    $this->click('link=Delete');
    // Wait for the response dialog
    $this->waitForText('css=#actionResponse .dialog_heading', 'Delete Favorite');
    // See if we got a successfull response
    $this->assertTextPresent('successful');
    // Close response dialog
    $this->click('css=div.close');
    // Re-Open favorites window
    $this->click('link=Favorites');
    $this->waitForElementVisible('id=favorites');
    // Make sure there isn't a link to view our deleted favorite
    $this->assertElementNotPresent('link=foobar');
    $this->click('css=div.close');
	}

  public function clickAndWaitFor($locator, $waitFor = 'id=favorites')
  {
    parent::clickAndWaitFor($locator, $waitFor);
  }

}
