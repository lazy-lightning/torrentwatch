<?php
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

  protected function assertPreConditions()
  {
    parent::assertPreConditions();
    $this->clickAndWaitFor('link=Configure', 'id=configuration', false);
    $this->assertVisible('id=global_config');
  }

  function testDefaultSave()
  {
    $this->assertElementPresent('link=Save');
    $this->clickAndWaitFor('link=Save', 'id=actionResponse', false);
    $this->assertText('id=actionResponse', 'Configuration saved.');
  }

  function testUpdateGlobalConfig()
  {
    $this->type('id=dvrConfig_webItemsPerLoad', 'qwerty');
    $this->clickAndWaitFor('link=Save', 'css=.errorSummary', false);
    $this->type('id=dvrConfig_webItemsPerLoad', 200);
    $this->clickAndWaitFor('link=Save', 'id=actionResponse', false);
    $this->assertText('id=actionResponse', 'Configuration saved.');
    $config = new dvrConfig;
    $config->init();
    $this->assertEquals($config->webItemsPerLoad, 200);
  }

  function testTorClient()
  {
    // click button to load torrent client configuration
    $this->click("xpath=id('configuration')/div[2]/ul/li[2]/a");
    $this->waitForElementPresentAndVisible('id=torClient');
    // Verify save to folder was default client
    $this->assertSelectedLabel('id=dvrConfig_torClient', 'Save to Folder');
    // change to clientTransRPC
    $this->select('id=dvrConfig_torClient', 'value=clientTransRPC');
    $this->waitForElementVisible('id=clientTransRPC');
    // and set a few variables
    $this->type("xpath=id('clientTransRPC')/div[3]/input", "spaztastic");
    $this->type("xpath=id('clientTransRPC')/div[4]/input", "pAsswOrd");
    // click save button
    $this->click("xpath=id('clientTransRPC')/div/a[1]");
    // not sure how to tell this is actually done
    usleep(500000);
    // Check that our selected client is visible
    $this->assertElementVisible('id=clientTransRPC');
    $this->assertSelectedLabel('id=dvrConfig_torClient', 'Transmission >= 1.3');
    // Check that our selected client was saved in the configuration
    $config = new dvrConfig;
    $config->init();
    $this->assertEquals($config->torClient, 'clientTransRPC');
    // Check that the data we entered was saved
    $this->assertEquals($config->clientTransRPC->username, 'spaztastic');
    $this->assertEquals($config->clientTransRPC->password, 'pAsswOrd');
  }

  function testNzbClient()
  {
    // click button to load torrent client configuration
    $this->click("xpath=id('configuration')/div[2]/ul/li[3]/a");
    $this->waitForElementPresentAndVisible('id=nzbClient');
    // Verify save to folder was default client
    $this->assertSelectedLabel('id=dvrConfig_nzbClient', 'Save to Folder');
    // change to clientSABnzbd
    $this->select('id=dvrConfig_nzbClient', 'value=clientSABnzbd');
    $this->waitForElementVisible('id=clientSABnzbd');
    // and set a few variables
    $this->type("xpath=id('clientSABnzbd')/div[1]/input", "spaztastic");
    // click save button
    $this->click("xpath=id('clientSABnzbd')/div/a[1]");
    // not sure how to tell this is actually done
    usleep(500000);
    // Check that our selected client is visible
    $this->assertElementVisible('id=clientSABnzbd');
    $this->assertSelectedLabel('id=dvrConfig_nzbClient', 'SABnzbd+');
    // Check that our selected client was saved in the configuration
    $config = new dvrConfig;
    $config->init();
    $this->assertEquals($config->nzbClient, 'clientSABnzbd');
    // Check that the data we entered was saved
    $this->assertEquals($config->clientSABnzbd->category, 'spaztastic');
  }

  function testFeeds()
  {
    // click button to load feeds configuration
    $this->click("xpath=id('configuration')/div[2]/ul/li[4]/a");
    $this->waitForElementPresentAndVisible('id=feeds');
    // Try entering a non-url and saving
    $this->type('id=feed_url', 'Ipsum');
    $this->click("xpath=id('newFeed')/form/a");
    // wait for error message
    $this->waitForElementPresentAndVisible('css=.errorSummary');
    // Try a valid sample feed
    // NOTE: bad reference to static data
    //       also requires a localhost.com to be defined in /etc/hosts
    $this->type('id=feed_url', 'http://localhost.com/nmtdvr/testing/index.html');
    $this->click("xpath=id('newFeed')/form/a");
    // wait for error message to disapear
    $this->waitForElementNotPresent('css=.errorSummary');
    // verify our sample feeds title is displayed
    $this->assertText("xpath=id('feeds')/div[1]/span", "Sample Feed");
    // verify feed items have not yet been loaded
    $this->assertElementNotPresent('css=.torrent');
    // close the dialog
    $this->click("css=#configuration > div.close");
    // wait for feed items to load
    $this->waitForElementPresent('css=.torrent');
    usleep(500000);
    // reopen the configuration dialog
    $this->clickAndWaitFor('link=Configure', 'id=configuration', false);
    // click the delete button
    $this->click("xpath=id('feeds')/div[1]/a");
    // wait till the feeds list is one element shorter
    $this->waitForElementNotPresent("xpath=id('feeds')/div[4]");
    usleep(500000);
    // close the dialog
    $this->click("css=#configuration > div.close");
    // verify the feed items have disapeared
    $this->waitForElementNotPresent('css=.torrent');
  }

  public function clickAndWaitFor($locator, $waitFor = 'id=configuration', $mid='id=progressbar')
  {
    parent::clickAndWaitFor($locator, $waitFor, $mid);
  }
}
