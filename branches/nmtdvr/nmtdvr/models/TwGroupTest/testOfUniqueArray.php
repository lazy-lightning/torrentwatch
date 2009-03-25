<?php

Mock::generatePartial('arrayItem', 'mockArrayItem', array());

class testOfUniqueArray extends UnitTestCase {

  var $uniqueArray;
  var $arrayItem;

  function setUp() {
    $this->callbackCount = 0;
    $this->uniqueArray = new uniqueArray();
    $this->arrayItem = new mockArrayItem();
  }

  function tearDown() {
    unset($this->uniqueArray);
    unset($this->arrayItem);
  }

  function testOfAdd() {
    $this->assertNotIdentical($this->uniqueArray->add($this->arrayItem), False);
  }
  
  function testOfDel() {
    $id = $this->uniqueArray->add($this->arrayItem);
    $this->uniqueArray->del($id);
    $this->assertFalse($this->uniqueArray->get($id));
  }

  function testOfGet() {
    $id = $this->uniqueArray->add($this->arrayItem);
    $this->assertEqual($id, $this->arrayItem->getId());
    $this->assertReference($this->uniqueArray->get($id), $this->arrayItem);
  }

  function testOfIsValidItem() {
    $this->assertTrue($this->uniqueArray->isValidArrayItem($this->arrayItem));
    $this->assertFalse($this->uniqueArray->isValidArrayItem($this));
  }

}

