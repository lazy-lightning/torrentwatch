<?php
/**
 * mediaTitleParserTest 
 * TODO: could use a mock factory and just ensure the proper functions were called
 *       with the right arguments
 * 
 * @uses CTestCase
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class mediaTitleParserTest extends CTestCase
{
  /**
   * testTitleParser
   * 
   * @dataProvider titleParserProvider
   * @param string $title the title to parse
   * @param string $shortTitle the expected tv show title
   * @param string $epTitle the expected episode title
   * @param integer $season the expected season
   * @param integer $episode the expected episode
   * @param array $qualitys the expected quality ids
   * @return void
   */
  public function testTitleParser($title, $shortTitle, $epTitle, $season, $episode, $qualitys)
  {
    $parser = new mediaTitleParser($title, Yii::app()->modelFactory);
    $this->assertType('tvEpisode', $parser->tvEpisode, $title);
    $this->assertEquals(null, $parser->movie, $title);
    $this->assertEquals(null, $parser->other, $title);
    $this->assertEquals($shortTitle, $parser->tvEpisode->tvShow->title, $title);
    $this->assertEquals($epTitle, $parser->tvEpisode->title, $title);
    $this->assertEquals($season, $parser->tvEpisode->season, $title);
    $this->assertEquals($episode, $parser->tvEpisode->episode, $title);
    foreach($qualitys as $qId)
      $this->assertContains($qId, $parser->quality, $title);
  }

  /**
   * titleParserProvider 
   * 
   * @return void
   */
  public function titleParserProvider()
  {
    $factory = Yii::app()->modelFactory;

    $ac3 = $factory->qualityByTitle('ac3')->id;
    $dsrip = $factory->qualityByTitle('dsrip')->id;
    $hdtv = $factory->qualityByTitle('hdtv')->id;
    $xvid = $factory->qualityByTitle('xvid')->id;

    return array(
        array(
          'BBC.The.Frankincense.Trail.1of4.Omen.Yemen.Saudi.Arabia.XviD.AC3.MVGroup.org.avi',
          'BBC The Frankincense Trail', 'Omen Yemen Saudi Arabia', 1, 1, array($xvid, $ac3)
        ),
        array(
          'Some.Show.E02.HDTV.XviD-XxXX',
          'Some Show', '', 1, 2, array($xvid, $hdtv),
        ),
        array(
          'Some.Show.EP02.HDTV.XviD-XxXX',
          'Some Show', '', 1, 2, array($xvid, $hdtv),
        ),
        array(
          'Some.Show.e02.HDTV.XviD-XxXX',
          'Some Show', '', 1, 2, array($xvid, $hdtv),
        ),
        array(
          'Some.Show.part2.HDTV.XviD-XxXX',
          'Some Show', '', 1, 2, array($xvid, $hdtv),
        ),
        array(
          'Some.Show.part2of4.HDTV.XviD-XxXX',
          'Some Show', '', 1, 2, array($xvid, $hdtv),
        ),
        array(
          'Some.Show.2of4.HDTV.XviD-XxXX',
          'Some Show', '', 1, 2, array($xvid, $hdtv),
        ),
        array(
          'Some.Show.2 of 4.HDTV.XviD-XxXX',
          'Some Show', '', 1, 2, array($xvid, $hdtv),
        ),
        array(
          'Some.Show.S07.HDTV.XviD-XxXX',
          'Some Show', '', 7, 0, array($xvid),
        ),
        array(
          'Some.Show.s07.HDTV.XviD-XxXX',
          'Some Show', '', 7, 0, array($xvid),
        ),
        array(
          'Some.Show.S07E11.HDTV.XviD-XxXX',
          'Some Show', '', 7, 11, array($hdtv, $xvid),
        ),
        array(
          'Some.Show.S07EP11.HDTV.XviD-XxXX',
          'Some Show', '', 7, 11, array($hdtv, $xvid),
        ),
        array(
          'Some.Show.s07e11.HDTV.XviD-XxXX',
          'Some Show', '', 7, 11, array($hdtv, $xvid),
        ),
        array(
          'Some.Show.7x11.HDTV.XviD-XxXX',
          'Some Show', '', 7, 11, array($hdtv, $xvid),
        ),
        array(
          'Some.Show.7 x 11.HDTV.XviD-XxXX',
          'Some Show', '', 7, 11, array($hdtv, $xvid),
        ),
        array(
          'Some.Show.711.HDTV.XviD-XxXX',
          'Some Show', '', 7, 11, array($hdtv, $xvid),
        ),
        array(
          'The.Every.Day.Show.01.05.2009.DSRip.XviD-xXX',
          'The Every Day Show', '', 0, 1231113600, array($dsrip, $xvid),
        ),
        array(
          'The.Every.Day.Show.2009.01.05.DSRip.XviD-xXX',
          'The Every Day Show', '', 0, 1231113600, array($dsrip, $xvid),
        ),
    );
  }

  /**
   * testApplyToSetsLastUpdated 
   * 
   * @return void
   */
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
    // With a lower dated pubDate the time shouldn't change
    $feedItem->pubDate = 200;
    $parser->applyTo($feedItem);
    $this->assertEquals(12345, $parser->tvEpisode->lastUpdated);
    $parser->tvEpisode->refresh();
    $this->assertEquals(12345, $parser->tvEpisode->lastUpdated);
    // With a higher date it should change again
    $feedItem->pubDate = 1234567;
    $parser->applyTo($feedItem);
    $this->assertEquals($feedItem->pubDate, $parser->tvEpisode->lastUpdated);
    $parser->tvEpisode->refresh();
    $this->assertEquals($feedItem->pubDate, $parser->tvEpisode->lastUpdated);

  }
}
