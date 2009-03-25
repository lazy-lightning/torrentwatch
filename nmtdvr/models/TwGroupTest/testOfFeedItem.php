<?php

class testOfFeedItem extends TwUnitTestCase {

  function setUp() {
     parent::setUp();
     $this->feedItem = $this->setUpFeedItem();
  }

  function testGuessTvData() {
    $items['Without.a.Trace.S07E11.HDTV.XviD-XxXX'] = array('shortTitle' => 'Without.a.Trace', 'quality' => 'HDTV', 'episode' => 11, 'season' => 7);
    $items['Without.a.Trace.7x11.HDTV.XviD-XxXX'] = array('shortTitle' => 'Without.a.Trace', 'quality' => 'HDTV', 'episode' => 11, 'season' => 7);
    $items['Without.a.Trace.7of11.HDTV.XviD-XxXX'] = array('shortTitle' => 'Without.a.Trace', 'quality' => 'HDTV', 'episode' => 7, 'season' => 1);
    $items['The.Daily.Show.01.05.2009.DSRip.XviD-xXX'] = array('shortTitle' => 'The.Daily.Show', 'quality' => 'DSRip', 'episode' => '01.05.2009', 'season' => 0);
    $items['Repossessed.WS.PDTV.XviD-FTP'] = array('shortTitle' => 'Repossessed.', 'quality' => 'PDTV', 'episode' => 0, 'season' => 0);
    $items['The.Diary.Of.Anne.Frank.Part2.HDTV.720p.HDTV.x264-XxX'] = array('shortTitle' => 'The.Diary.Of.Anne.Frank', 'quality' => 'HDTV', 'episode' => 2, 'season' => 1);
    $items['The.Diary.Of.Anne.Frank.Part2of6.720p.HDTV.x264-XxX'] = array('shortTitle' => 'The.Diary.Of.Anne.Frank', 'quality' => '720p', 'episode' => 2, 'season' => 1);
    foreach($items as $title => $item) {
      $result = $this->feedItem->guessTvData($title);
      if($this->assertIsA($result, 'array')) {
        $this->assertEqual($result['shortTitle'], $item['shortTitle'], 'shortTitle: '.$result['shortTitle'].' == '.$item['shortTitle'].": $title");
        $this->assertEqual($result['quality'], $item['quality'], 'quality: '.$result['quality'].' == '.$item['quality'].": $title");
        $this->assertEqual($result['episode'], $item['episode'], "episode guess for $title is ".$result['episode']);
        $this->assertEqual($result['season'], $item['season'], "season guess for $title is ".$result['season']);
      }
    }
  }

}

