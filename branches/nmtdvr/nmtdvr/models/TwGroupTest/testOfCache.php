<?php

class testOfCache extends TwUnitTestCase {

  var $sampledata = "jhasdfaALsdf:3412#$1234askdlfkasdfq2341";

  function testPut() {
    DataCache::Put('unittest', '12345', 60, $this->sampledata);
    $this->assertTrue(DataCache::isCached('unittest', '12345'), "isCached");
  }

  function testRetreive() {
    DataCache::Put('unittest', '12345', 60, $this->sampledata);
    $data = DataCache::Get('unittest', '12345');
    $this->assertEqual($this->sampledata, $data);
  }
}
?>
