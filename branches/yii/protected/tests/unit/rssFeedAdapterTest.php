<?php

Yii::import('application.components.feedAdapters.*');
require_once('simplepie.inc');
require_once('rssFeedAdapter.php');

class rssFeedAdapterTest extends DbTestCase
{
  public $fixtures = array(
      'feeds' => 'feed',
  );

  // object under test
  protected $adapter;

  // mocked simplepie object
  protected $simplePie;

  // will be returned as result of $simplePie->get_items();
  protected $get_items;

  public function assertPreConditions()
  {
    error_reporting(E_ALL);
    $this->get_items = array(
        $this->fakeItem(12345, 'first'),
        $this->fakeItem(22222, 'second'),
        $this->fakeItem(33333, 'third'),
        $this->fakeItem(44444, 'fourth'),
    );

    $this->simplePie = $this->getMock('SimplePie', array('init', 'get_items', 'set_feed_url', 'error'));
    $this->feed = (object)$this->feeds['all'];
    // set to -1 so anything testing if status was set will know it wasn't already preset
    $this->feed->status = -1;
    $this->simplePie->expects($this->once())->method('set_feed_url')
              ->with($this->equalTo($this->feed->url));
    $this->adapter = new rssFeedAdapter($this->feed, null, $this->simplePie);
    $this->adapter->init();

    parent::assertPreConditions();
  }

  protected function fakeItem($id, $title)
  {
    $mock = $this->getMock('feedAdapter_Item', array(), array(), '', false);
    $mock->expects($this->any())->method('get_id')
         ->will($this->returnValue($id));
    $mock->expects($this->any())->method('get_title')
         ->will($this->returnValue($title));
    return $mock;
  }

  public function testInit()
  {
    $this->simplePie->expects($this->once())->method('init');
    $this->adapter->init();
  }

  public function testBasicRun()
  {
    $this->simplePie->expects($this->once())
              ->method('get_items')
              ->will($this->returnValue($this->get_items));

    $factory = $this->getMock('modelFactory');
    $factory->expects($this->exactly(4))->method('feedItemByAttributes');

    $this->adapter->checkFeedItems($factory);
    $this->assertEquals(feed::STATUS_OK, $this->feed->status);
  }

  public function testExcludeHash()
  {
    $this->simplePie->expects($this->once())
              ->method('get_items')
              ->will($this->returnValue($this->get_items));

    // import the hashes of 2 of the feedItems into the db
    $cmd = Yii::app()->db->createCommand(
        'INSERT INTO feedItem (status, hash, feed_id, pubDate, lastUpdated) VALUES(999, :hash, 0, 0, 0)'
    );
    $cmd->bindValue(':hash', md5($this->get_items[0]->get_id()))->execute();
    $cmd->bindValue(':hash', md5($this->get_items[1]->get_id()))->execute();

    $factory = $this->getMock('modelFactory');
    // with 2 feedItems already having their hash only the other 2 should match
    $factory->expects($this->exactly(2))->method('feedItemByAttributes');

    $this->adapter->checkFeedItems($factory);
    $this->assertEquals(feed::STATUS_OK, $this->feed->status);

    // revert changes that annoy other tests
    Yii::app()->db->createCommand('DELETE FROM feedItem WHERE status=999')->execute();
  }

  public function testSimplePieError()
  {
    $this->simplePie->expects($this->atLeastOnce())
         ->method('error')
         ->will($this->returnValue(true));
    $this->assertFalse($this->adapter->init(), 'adapter init');
    $this->assertEquals(feed::STATUS_ERROR, $this->feed->status);
  }
}


