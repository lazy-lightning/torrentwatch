<?php

class HistoryTest extends WebTestCase
{
  protected $fixtures = array(
      'history'=>'history',
      'tvEpisode'=>'tvEpisode',
  );

  protected $locators = array(
      'clearHistoryButton'   => "id=clearHistory",
      'historyItem'          => "xpath=id('history')/div/ul/li[1]",
      'historyItemTitle'     => "xpath=id('history')/div/ul/li[1]/span",
      'toggleHistoryButton'  => "xpath=id('view')/a",
  );

  public function assertPreConditions()
  {
    $l = $this->locators;
    parent::assertPreConditions();
    $this->click($l['toggleHistoryButton']);
    $this->waitForElementPresentAndVisible($l['clearHistoryButton']);
  }

  public function testClearHistory()
  {
    $l = $this->locators;
    // Check that our fixture history item exists
    $this->assertElementPresent($l['historyItem']");
    // Check that the title is being displayed
    $this->assertText($l['historyItemTitle'], $this->history[0]['feedItem_title']);
    // Click the first item to show some details
    $this->click($l['historyItem'];);
    // Check if our details are being displayed
    $this->assertText($l['historyItem'], $this->history[0]['feed_title']);
    $this->assertText($l['historyItem'], $this->history[0]['favorite_name']);
    // click the clear
    $this->click($l['clearHistoryButton']);
    // Wait for the history item to be removed from page
    $this->waitForElementNotPresent($l['historyItem']);
  }

}
