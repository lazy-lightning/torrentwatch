<?php

/**
 * mediaTitleParserTest 
 * 
 * @uses CUnitTest
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
  * TODO: could use a mock factory and just ensure the proper functions were called
  *       with the right arguments
  */
class mediaTitleParserTest extends CTestCase
{
  public function testConstruct()
  {
    $factory = Yii::app()->modelFactory;
    $parser = new mediaTitleParser('BBC.The.Frankincense.Trail.1of4.Omen.Yemen.Saudi.Arabia.XviD.AC3.MVGroup.org.avi', $factory);
    $this->assertEquals('BBC The Frankincense Trail', $parser->shortTitle);
    $this->assertEquals(1, $parser->season);
    $this->assertEquals(1, $parser->episode);
    $this->assertEquals('Omen Yemen Saudi Arabia', $parser->epTitle);
  }

  public function testEpisodeOnly()
  {
    $titles = array(
        'Some.Show.E02.HDTV.XviD-XxXX',
        'Some.Show.EP02.HDTV.XviD-XxXX',
        'Some.Show.e02.HDTV.XviD-XxXX',
        'Some.Show.part2.HDTV.XviD-XxXX',
        'Some.Show.part2of4.HDTV.XviD-XxXX',
        'Some.Show.2of4.HDTV.XviD-XxXX',
        'Some.Show.2 of 4.HDTV.XviD-XxXX',
    );
    $factory = Yii::app()->modelFactory;
    $hdtv = $factory->qualityByTitle('HDTV');
    $xvid = $factory->qualityByTitle('XViD');

    foreach($titles as $title)
    {
      $parser = new mediaTitleParser($title, $factory);
      $this->assertType('tvEpisode', $parser->tvEpisode, $title);
      $this->assertEquals(null, $parser->movie, $title);
      $this->assertEquals(null, $parser->other, $title);
      $this->assertEquals('Some Show', $parser->tvEpisode->tvShow->title, $title);
      $this->assertEquals(1, $parser->tvEpisode->season, $title);
      $this->assertEquals(2, $parser->tvEpisode->episode, $title);
      $this->assertContains($hdtv->id, $parser->quality, $title);
      $this->assertContains($xvid->id, $parser->quality, $title);
    }
  }

  public function testSeasonOnly()
  {
    $titles = array(
        'Some.Show.S07.HDTV.XviD-XxXX',
        'Some.Show.s07.HDTV.XviD-XxXX',
    );
    $factory = Yii::app()->modelFactory;
    $hdtv = $factory->qualityByTitle('HDTV');
    $xvid = $factory->qualityByTitle('XViD');

    foreach($titles as $title)
    {
      $parser = new mediaTitleParser($title, $factory);
      $this->assertType('tvEpisode', $parser->tvEpisode, $title);
      $this->assertEquals(null, $parser->movie, $title);
      $this->assertEquals(null, $parser->other, $title);
      $this->assertEquals('Some Show', $parser->tvEpisode->tvShow->title, $title);
      $this->assertEquals(7, $parser->tvEpisode->season, $title);
      $this->assertEquals(0, $parser->tvEpisode->episode, $title);
      $this->assertContains($hdtv->id, $parser->quality, $title);
      $this->assertContains($xvid->id, $parser->quality, $title);
    }
  }

  public function testFullEpisode()
  {
    $titles = array(
        'Some.Show.S07E11.HDTV.XviD-XxXX',
        'Some.Show.S07EP11.HDTV.XviD-XxXX',
        'Some.Show.s07e11.HDTV.XviD-XxXX',
        'Some.Show.7x11.HDTV.XviD-XxXX',
        'Some.Show.7 x 11.HDTV.XviD-XxXX',
        'Some.Show.711.HDTV.XviD-XxXX',
    );
    $factory = Yii::app()->modelFactory;
    $hdtv = $factory->qualityByTitle('HDTV');
    $xvid = $factory->qualityByTitle('XViD');

    foreach($titles as $title)
    {
      $parser = new mediaTitleParser($title, $factory);
      $this->assertType('tvEpisode', $parser->tvEpisode, $title);
      $this->assertEquals(null, $parser->movie, $title);
      $this->assertEquals(null, $parser->other, $title);
      $this->assertEquals('Some Show', $parser->tvEpisode->tvShow->title, $title);
      $this->assertEquals(7, $parser->tvEpisode->season, $title);
      $this->assertEquals(11, $parser->tvEpisode->episode, $title);
      $this->assertContains($hdtv->id, $parser->quality, $title);
      $this->assertContains($xvid->id, $parser->quality, $title);
    }
  }

  public function testDateEpisode()
  {
    $titles = array(
        'The.Every.Day.Show.01.05.2009.DSRip.XviD-xXX',
        'The.Every.Day.Show.2009.01.05.DSRip.XviD-xXX',
    );
    $factory = Yii::app()->modelFactory;
    $dsrip = $factory->qualityByTitle('DSRip');
    $xvid = $factory->qualityByTitle('XViD');
  
    foreach($titles as $title)
    {
      $parser = new mediaTitleParser($title, $factory);
      $this->assertType('tvEpisode', $parser->tvEpisode, $title);
      $this->assertEquals(null, $parser->movie, $title);
      $this->assertEquals(null, $parser->other, $title);
      $this->assertEquals('The Every Day Show', $parser->tvEpisode->tvShow->title, $title);
      $this->assertEquals(0, $parser->tvEpisode->season, $title);
      // Jan 05 2009 at midnight UTC 
      $this->assertEquals(1231113600, $parser->tvEpisode->episode, $title);
      $this->assertContains($dsrip->id, $parser->quality, $title);
      $this->assertContains($xvid->id, $parser->quality, $title);
    }
  }

  public function testApplyToSetsLastUpdated()
  {
    tvEpisode::model()->deleteAll();
    $feedItem = new feedItem;
    $feedItem->pubDate = 12345;
    $parser = new mediaTitleParser('foobar.s01e01.hdtv');
    $parser->applyTo($feedItem);
    $this->assertEquals($feedItem->pubDate, $parser->tvEpisode->lastUpdated);
    $parser->tvEpisode->refresh();
    $this->assertEquals($feedItem->pubDate, $parser->tvEpisode->lastUpdated);
  }
}
