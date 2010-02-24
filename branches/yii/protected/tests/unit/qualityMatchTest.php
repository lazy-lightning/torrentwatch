<?php

Yii::import('application.components.mediaTitleParser.qualityMatch');

class qualityMatchTest extends CTestCase 
{
  public function testSplitBasic()
  {
    list($short, $qual) = qualityMatch::run('Foo.Bar.iNT.HDTV');
    $this->assertEquals('Foo.Bar', $short);
    $this->assertEquals(2, count($qual));
    $this->assertContains('iNT', $qual);
    $this->assertContains('HDTV', $qual);
  }

  public function testDontMatchInsideTitle()
  {
    // The bug was that the quality of 'iNT' short for 'internal' 
    // would cut this into $short = 'FOO'
    list($short, $qual) = qualityMatch::run('FOOintBAR.1080p');
    $this->assertEquals('FOOintBAR', $short);
    $this->assertEquals(1, count($qual));
    $this->assertContains('1080p', $qual);
  }

  public function testQualityInBraces()
  {
    list($short, $qual) = qualityMatch::run('Foo.Bar_-_(720p)(repack)');
    $this->assertEquals('Foo.Bar', $short);
    $this->assertEquals(2, count($qual));
    $this->assertContains('repack', $qual);
    $this->assertContains('720p', $qual);
  }
}
