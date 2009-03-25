<?php

Mock::generate('historyItem', 'MockHistoryItem');

class testOfHistory extends TwUnitTestCase {

  var $season, $episode, $historyItem, $shortTitle, $history;

  function setUp() {
    parent::setUp();
    $this->historyItem = new MockHistoryItem();
    $this->historyItem->season = $this->season = 3;
    $this->historyItem->episode = $this->episode = 12;
    $this->historyItem->shortTitle = $this->shortTitle = 'Sample Title';
    $this->history = new history();
  }
  
  function tearDown() {
    parent::tearDown();
  }

  function testAdd() {
    $hItem = $this->historyItem;
    $id = $this->history->add($hItem);
    $this->assertNotIdentical($id, False, 'valid id');
    $this->assertTrue($this->history->previouslyDownloadedEpisode($hItem->shortTitle, $hItem->season, $hItem->episode), 'item is cached');
  }

  function testPreviouslyDownloadedEpisode() {
    $hItem = $this->historyItem;
    $id = $this->history->add($hItem);
    $this->assertTrue($this->history->previouslyDownloadedEpisode($hItem->shortTitle, $hItem->season, $hItem->episode), 'item is cached');
    $this->assertFalse($this->history->previouslyDownloadedEpisode($hItem->shortTitle, $hItem->season+1, $hItem->episode));
    $this->assertFalse($this->history->previouslyDownloadedEpisode($hItem->shortTitle, $hItem->season, $hItem->episode+1));
    $this->assertFalse($this->history->previouslyDownloadedEpisode('new title', $hItem->season, $hItem->episode));
  }

  function testSave() {
    $hItem = $this->historyItem;
    $this->history->add($hItem);
    $this->assertTrue($this->history->previouslyDownloadedEpisode($hItem->shortTitle, $hItem->season, $hItem->episode), 'pre-save: %s');
    unset($this->history);
    $this->history = new history();
    $this->assertTrue($this->history->previouslyDownloadedEpisode($hItem->shortTitle, $hItem->season, $hItem->episode), 'post-save: %s');
  }

  function testDelete() {
    $hItem = $this->historyItem;
    $id = $this->history->add($hItem);
    // Should not allow you to delete anything
    $this->assertFalse($this->history->del($id));
    $this->assertTrue($this->history->previouslyDownloadedEpisode($hItem->shortTitle, $hItem->season, $hItem->episode));
    $this->assertReference($this->history->get($id), $hItem);
  }
}
?>
