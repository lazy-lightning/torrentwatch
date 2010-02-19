<?php
/**
 * Functional testing for the configuration dialog
 * 
 * @uses WebTestCase
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class DvrConfigTest extends WebTestCase
{
  public $autoStop = false;

  // need many fixtures to constantly reset any new saved feeds
  protected $fixtures = array(
      'dvrConfig'=>':dvrConfig',
      'feed'=>'feed',
      'feedItem'=>'feedItem',
      'other'=>'other',
      'movie'=>'movie',
      'tvEpisode'=>'tvEpisode',
  );

  // These are element locators  used throughout the test
  // they offer a single place to change a locator and 
  // more obvious names
  protected $locators = array(
      'closeConfigDialogButton'    => "css=#configuration > div.close",
      'configDialog'               => 'id=configuration',
      'deleteFirstFeedButton'      => "xpath=id('feeds')/div[1]/a",
      'errorSummary'               => 'css=.errorSummary',
      'feedSaveButton'             => "xpath=id('newFeed')/form/a",
      'feedsTab'                   => 'id=feeds',
      'feedUrlInput'               => 'id=feed_url',
      'feedItem'                   => 'css=#feedItems_container .torrent',
      'firstFeedTitle'             => "xpath=id('feeds')/div[1]/span",
      'globalConfigTab'            => 'id=global_config',
      'itemsPerLoadInput'          => 'id=dvrConfig_webItemsPerLoad',
      'nzbClientSelect'            => 'id=dvrConfig_nzbClient',
      'nzbTab'                     => 'id=nzbClient',
      'savedResponse'              => 'css=.saved',
      'toggleConfigDialog'         => 'link=Configure',
      'toggleFeedTab'              => "xpath=id('configuration')/div[2]/ul/li[4]/a",
      'toggleNzbTab'               => "xpath=id('configuration')/div[2]/ul/li[3]/a",
      'toggleTorrentTab'           => "xpath=id('configuration')/div[2]/ul/li[2]/a",
      'torClientSelect'            => 'id=dvrConfig_torClient',
      'torrentTab'                 => 'id=torClient',
      'transRpcUsernameInput'      => "xpath=id('clientTransRPC')/div[3]/input",
      'transRpcPasswordInput'      => "xpath=id('clientTransRPC')/div[4]/input",
      'transRpcSaveButton'         => "xpath=id('clientTransRPC')/div/a[1]",
      'sabnzbdCategoryInput'       => "xpath=id('clientSABnzbd')/div[1]/input",
      'sabnzbdConfig'              => 'id=clientSABnzbd',
      'sabnzbdSaveButton'          => "xpath=id('clientSABnzbd')/div/a[1]",

  );

  /**
   * Open the configuration dialog before each test procedes
   * 
   * @return void
   */
  protected function assertPreConditions()
  {
    $l = $this->locators; // shorthand access
    parent::assertPreConditions();
    $this->clickAndWaitFor($l['toggleConfigDialog'], $l['configDialog'], false);
    $this->assertVisible($l['globalConfigTab']);
  }

  /**
   * Test a save of the data pre-populated in the form
   * 
   * @return void
   */
  public function testDefaultSave()
  {
    $l = $this->locators; // shorthand access
    $this->assertElementPresent('link=Save');
    $this->clickAndWaitFor('link=Save', $l['savedResponse'], false);
    $this->assertText($l['savedResponse'], 'Saved');
  }

  /**
   * Test the main tab of the configuration dialog
   * 
   * @return void
   */
  public function testUpdateGlobalConfig()
  {
    $l = $this->locators; // shorthand access
    $this->type($l['itemsPerLoadInput'], 'qwerty');
    $this->clickAndWaitFor('link=Save', $l['errorSummary'], false);
    $this->type($l['itemsPerLoadInput'], 200);
    $this->clickAndWaitFor('link=Save', $l['savedResponse'], false);
    $this->assertText($l['savedResponse'], 'Saved');
    $config = new dvrConfig;
    $config->init();
    $this->assertEquals($config->webItemsPerLoad, 200);
  }

  /**
   * Test the torrent client tab of the configuration dialog
   * 
   * @return void
   */
  public function testTorClient()
  {
    $l = $this->locators; // shorthand access
    // click button to load torrent client configuration
    $this->click($l['toggleTorrentTab']);
    $this->waitForElementPresentAndVisible($l['torrentTab']);
    // Verify save to folder was default client
    $this->assertSelectedLabel($l['torClientSelect'], 'Save to Folder');
    // change to clientTransRPC
    $this->select($l['torClientSelect'], 'value=clientTransRPC');
    $this->waitForElementVisible('id=clientTransRPC');
    // and set a few variables
    $this->type($l['transRpcUsernameInput'], "spaztastic");
    $this->type($l['transRpcPasswordInput'], "pAsswOrd");
    // click save button
    $this->click($l['transRpcSaveButton']);
    // not sure how to tell this is actually done
    usleep(500000);
    // Check that saved client choice is selected
    $this->assertSelectedLabel($l['torClientSelect'], 'Transmission >= 1.3');
    // Check that our selected client options are visible
    $this->assertElementVisible('id=clientTransRPC');
    // Check that our selected client was saved in the configuration
    $config = new dvrConfig;
    $config->init();
    $this->assertEquals($config->torClient, 'clientTransRPC');
    // Check that the data we entered was saved
    $this->assertEquals($config->clientTransRPC->username, 'spaztastic');
    $this->assertEquals($config->clientTransRPC->password, 'pAsswOrd');
  }

  /**
   * Test the nzb client tab of the configuration dialog
   * 
   * @return void
   */
  function testNzbClient()
  {
    $l = $this->locators; // shorthand access
    // click button to load torrent client configuration
    $this->click($l['toggleNzbTab']);
    $this->waitForElementPresentAndVisible($l['nzbTab']);
    // Verify save to folder was default client
    $this->assertSelectedLabel($l['nzbClientSelect'], 'Save to Folder');
    // change to clientSABnzbd
    $this->select($l['nzbClientSelect'], 'value=clientSABnzbd');
    $this->waitForElementVisible($l['sabnzbdConfig']);
    // and set a few variables
    $this->type($l['sabnzbdCategoryInput'], "spaztastic");
    // click save button
    $this->click($l['sabnzbdSaveButton']);
    // not sure how to tell this is actually done
    usleep(500000);
    // Check that our selected client is visible
    $this->assertElementVisible($l['sabnzbdConfig']);
    $this->assertSelectedLabel($l['nzbClientSelect'], 'SABnzbd+');
    // Check that our selected client was saved in the configuration
    $config = new dvrConfig;
    $config->init();
    $this->assertEquals($config->nzbClient, 'clientSABnzbd');
    // Check that the data we entered was saved
    $this->assertEquals($config->clientSABnzbd->category, 'spaztastic');
  }

  /**
   * Test the feeds tab of the configuration dialog
   * 
   * @return void
   */
  function testFeeds()
  {
    $l = $this->locators; // shorthand access
    // click button to load feeds configuration
    $this->click($l['toggleFeedTab']);
    $this->waitForElementPresentAndVisible($l['feedsTab']);
    // Try entering a non-url and saving
    $this->type($l['feedUrlInput'], 'Ipsum');
    $this->click($l['feedSaveButton']);
    // wait for error message
    $this->waitForElementPresentAndVisible($l['errorSummary']);
    // Try a valid sample feed
    // NOTE: bad reference to static data
    //       also requires a localhost.com to be defined in /etc/hosts
    $this->type($l['feedUrlInput'], 'http://localhost.com/nmtdvr/testing/index.html');
    $this->click($l['feedSaveButton']);
    // wait for error message to disapear
    $this->waitForElementNotPresent($l['errorSummary']);
    // verify our sample feeds title is displayed
    $this->assertText($l['firstFeedTitle'], "Sample Feed");
    // verify feed items have not yet been loaded
    $this->assertElementNotPresent($l['feedItem']);
    // close the dialog
    $this->click($l['closeConfigDialogButton']);
    // wait for feed items to load
    $this->waitForElementPresent($l['feedItem']);
    usleep(500000);
    // reopen the configuration dialog
    $this->clickAndWaitFor($l['toggleConfigDialog'], $l['configDialog'], false);
    // click the delete button
    $this->click($l['deleteFirstFeedButton']);
    // wait till the feeds list is one element shorter
    $this->waitForElementNotPresent("xpath=id('feeds')/div[4]");
    usleep(500000);
    // close the dialog
    $this->click($l['closeConfigDialogButton']);
    // verify the feed items have disapeared
    $this->waitForElementNotPresent($l['feedItem']);
  }

  /**
   * Overloaded to set sensible defaults for second and third parameter
   * 
   * @param string $locator 
   * @param string $waitFor 
   * @param string $mid 
   * @return void
   */
  public function clickAndWaitFor($locator, $waitFor = 'id=configuration', $mid='id=progressbar')
  {
    parent::clickAndWaitFor($locator, $waitFor, $mid);
  }
}