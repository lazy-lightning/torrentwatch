<?php

Yii::import('application.components.mediaTitleParser.qualityMatch');

class qualityMatchTest extends CTestCase 
{
  /**
   * testQualityMatch 
   * 
   * @dataProvider provider
   * @param string $title the title to test
   * @param string $short the expected short title
   * @param array $qual the expected string qualitys
   * @return void
   */
  public function testQualityMatch($title, $short, $qual)
  {
    $result = qualityMatch::run($title);
    $this->assertEquals($short, $result[0], $title);
    $this->assertEquals(count($qual), count($result[1]), $title);
    foreach($qual as $text)
      $this->assertContains($text, $qual, $title);
  }

  /**
   * provider 
   * 
   * @return void
   */
  public function provider()
  {
    return array(
        array('Foo.Bar.iNT.HDTV', 'Foo.Bar', array('iNT', 'HDTV')),
        array('Foo.Bar_-_(720p)(repack)', 'Foo.Bar', array('720p', 'repack')),
        // The bug was that the quality of 'iNT' short for 'internal' 
        // would cut this into $short = 'FOO'
        array('FOOintBAR.1080p', 'FOOintBAR', array('1080p')),
    );
  }
  
}
