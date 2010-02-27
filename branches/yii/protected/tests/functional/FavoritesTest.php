<?php
/**
 * Functional testing of the favorites dialog
 * 
 * @uses WebTestCase
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class FavoritesTest extends WebTestCase
{
  /**
   * @var array fixtures to be used for this test case (name=>ar class OR :table)
   */
  protected $fixtures = array(
      'favoriteTvShow'=>'favoriteTvShow',
      'favoriteMovie'=>'favoriteMovie',
      'favoriteString'=>'favoriteString',
      'tvEpisode'=>'tvEpisode',
  );

  /**
   * @var array locators used in the test (name=>locator)
   */
  protected $locators = array(
      'actionResponseDialog'    => 'id=actionResponse',
      'actionResponseHeading'   => 'css=#actionResponse .dialog_heading',
      'createdFavoriteLink'     => "xpath=id('{type}-li-1')/a",
      'createFavoriteButton'    => 'link=Create',
      'deleteFavoriteButton'    => 'link=Delete',
      'errorSummary'            => 'css=.errorSummary',
      'favoriteFilterInput'     => 'name={type}[filter]',
      'favoriteMovieContainer'  => 'id=favoriteMovie_container',
      'favoriteNameInput'       => "name={type}[{id}]",
      'favoriteSaveInInput'     => "css=#{type}-1 .favorite_savein input",
      'favoriteStringContainer' => 'id=favoriteString_container',
      'favoriteQualitySelect'   => "{}div[@class='favorite_quality']/select",
      'favoritesDialog'         => 'id=favorites',
      'favoriteForm'            => '{}div',
      'newFavoriteLink'         => "xpath=id('{type}-li-')/a",
      'saveFavoriteButton'      => '{}div/a[1]',
      'saved'                   => "{}x:div[@class='saved']",
      'toggleFavoriteMovieTab'  => "xpath=id('favorites')/div[2]/ul/li[2]/a",
      'toggleFavoriteStringTab' => "xpath=id('favorites')/div[2]/ul/li[3]/a",
      'toggleFavoritesButton'   => 'link=Favorites',
      'updateFavoriteButton'    => 'link=Update',
  );

  /**
   * open the favorites dialog before performing each test
   * 
   * @return void
   */
  public function assertPreConditions()
  {
    $l = $this->locators;
    parent::assertPreConditions();
    $this->click($l['toggleFavoritesButton']);
    $this->waitForElementVisible($l['favoritesDialog']);
  }

  /**
   * getLocators returns a set of locators targeted for the current test
   * 
   * @param string $type the type of favorite to locate
   * @param int $id the name of the related foreign key in favorite class being
   *                tested as it appears in the form
   * @return array locators used in the test (name=>locator)
   */
  protected function getLocators($type,$id) {
    $xpath = "xpath=id('$type-1')/";
    $out = array();
    foreach($this->locators as $key =>$locator)
        $out[$key] = str_replace(array('{}','{type}','{id}'), array($xpath,$type,$id), $locator);
    return $out;
  }

  /**
   * Performs a test to verify that our floated elements ended up lined up properly
   * and didn't overflow to the next line
   * 
   * @return void
   */
  public function testTvShowRender()
  {
    $l = $this->getLocators('favoriteTvShow', 'tvShow_id');
    $this->click($l['newFavoriteLink']);
    $this->waitForElementPresentAndVisible($l['createFavoriteButton']);
    $this->assertElementPositionTop('id=favoriteTvShow_saveIn', 
        $this->getElementPositionTop('css=#favoriteTvShow- .favorite_savein label'));
    $this->assertElementPositionTop('id=quality_id_1',
        $this->getElementPositionTop('id=quality_id_3'));
  }

  /**
   * Performs a test of the favorites TV Show tab
   * 
   * @return void
   */
  public function testTvShowFavorite()
  {
    // tvShow will be open be default
    $this->realTest($this->getLocators('favoriteTvShow', 'tvShow_id'));
  }

  /**
   * Performs a test of the favorites TV Show tab
   * 
   * @return void
   */
  public function testMovieFavorite()
  {
    $l = $this->getLocators('favoriteMovie', 'name');
    // Click the movies anchor
    $this->click($l['toggleFavoriteMovieTab']);
    usleep(500000);
    $this->waitForElementPresentAndVisible($l['favoriteMovieContainer']);
    $this->realTest($l);
  }

  /**
   * Performs a test of the favorites Strings tab
   * 
   * @access public
   * @return void
   */
  function testStringFavorite()
  {
    $l = $this->getLocators('favoriteString', 'name');
    // Click the strings anchor
    $this->click($l['toggleFavoriteStringTab']);
    usleep(500000);
    $this->waitForElementPresentAndVisible($l['favoriteStringContainer']);
    $this->realTest($l);
  }

  /**
   * Performs a basic test of a favorites tab using the provided locators.
   * tests of: 
   * favorite creation
   * ensure an errorSummary is present if bad data is supplied to Save In 
   * - TODO: this assumes it will appear for others.  should test
   * ensure supplied data is returned after create/update
   * ensure various links automatically appear/disapear when creating/deleting favorites
   * verifys can set and unset multiple qualitys 
   * favorite deletion
   *
   * @param array $locators locators used in the test (name=>locator)
   * @return void
   */
	public function realTest($locators)
	{
    $l = $locators;
    $this->assertElementPresent($l['newFavoriteLink']);
    // Click the new favorite link and wait for form to load
    $this->click($l['newFavoriteLink']);
    $this->waitForElementPresentAndVisible($l['createFavoriteButton']);
    // Enter a name
    $this->type($l['favoriteNameInput'],'foobar');
    // favoriteString also requires a filter
    if($this->isElementPresent($l['favoriteFilterInput']))
      $this->type($l['favoriteFilterInput'], 'bazzab');
    // click create and wait for an update button to appear
    $this->clickAndWaitFor($l['createFavoriteButton'], $l['updateFavoriteButton'],false);
    // Make sure item created successfully
    $this->assertElementNotPresent($l['errorSummary']);
    // Make sure the creation form was hidden
    $this->assertElementNotVisible($l['createFavoriteButton']);
    // Make sure the creation form is empty
    $this->assertText($l['favoriteNameInput'], '');
    // Make sure a listitem was made for our favorite
    $this->assertElementPresent($l['createdFavoriteLink']); 
    // Make sure the creation form was reset
    $this->assertValue($l['favoriteNameInput'], '');
    // Make sure our name got to this page
    // NOTE: not working on movies/strings even though the text is visible by human
    // $this->assertTextPresent('foobar');
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
    $this->click($l['updateFavoriteButton']);
    $this->waitForElementPresent($l['saved']);
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

  /**
   * Overload to provide sensible defaults to second and third parameter
   * 
   * @param string $locator element to be clicked
   * @param string $waitFor element to wait for
   * @param string $mid optional element to wait for before second argument
   * @return void
   */
  public function clickAndWaitFor($locator, $waitFor = 'id=favorites', $mid='id=progressbar')
  {
    parent::clickAndWaitFor($locator, $waitFor, $mid);
  }

}
