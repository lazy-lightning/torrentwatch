<?php

Mock::generatePartial('cacheItem', 'mockCacheItem', array());

class testOfCachedArray extends TwUnitTestCase {

  var $cachedArry;
  var $cacheItem;
  var $callbackCount;

  function setUp() {
    parent::setUp();
    $this->callbackCount = 0;
    $this->cachedArray = new cachedArray('mockCacheItem', __CLASS__);
    $this->cacheItem = new mockCacheItem();
  }

  function tearDown() {
    @exec('rm /home/torrentwatch/branches/OO/userdata/DataCache/tw_unittest*');
    parent::tearDown();
  }

  function testIsValidItem() {
    $this->assertTrue($this->cachedArray->isValidArrayItem(new mockCacheItem));
    $this->assertFalse($this->cachedArray->isValidArrayItem(new mockArrayItem));
  }

  function testOfAddDel() {
    $id = $this->cachedArray->add($this->cacheItem);
    unset($this->cachedArray);
    $this->cachedArray = new cachedArray('mockCacheItem', __CLASS__);
    $this->assertNotIdentical($this->cachedArray->get($id), False, 'Add: %s');

    $this->cachedArray->del($id);
    unset($this->cachedArray);
    $this->cachedArray = new cachedArray('mockCacheItem', __CLASS__);
    $this->AssertFalse($this->cachedArray->get($id), 'Del: %s');
  }

}

