<?php

class HistoryTest extends WebTestCase
{
  protected $fixtures = array(
      'history'=>'history',
  );

  public function assertPreConditions()
  {
    parent::assertPreConditions();
    $this->click("xpath=id('view')/a");
    $this->waitForElementPresentAndVisible('id=clearHistory');
  }

  public function testClearHistory()
  {
    // Check that our fixture history item exists
    $this->assertElementPresent("xpath=id('history')/div/ul/li");
    // Check that the title is being displayed
    $this->assertText("xpath=id('history')/div/ul/li[1]/span", $this->history[0]['feedItem_title']);
    // click the clear
    $this->click("xpath=id('clearHistory')");
    // Wait for the history item to be removed from page
    $this->waitForElementNotPresent("xpath=id('history')/div/ul/li");

  }
}
