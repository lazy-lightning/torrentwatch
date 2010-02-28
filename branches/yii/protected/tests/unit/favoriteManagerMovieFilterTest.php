<?php

class favoriteManagerMovieFilterTest extends favoriteManagerFilterTest
{
  public $testClass = 'favoriteMovie';

  public $fixtures = array(
      'favoriteMovie'=>'favoriteMovie',
      'movie'=>':movie',
      'feedItem'=>':feedItem',
      'feed'=>':feed',
      'dvrConfig'=>':dvrConfig',
  );

  /**
   * testMovie 
   * 
   * @dataProvider provider
   * @param array $attributes attributes to set in the favoriteMovie  ( attribute1 => value1, attribute2 => value2, ... )
   * @param array $expectedStatus feedItem status count ( status1 => expectedCount1, status2 => expectedCount2, ... ) 
   * @return void
   */
  public function testMovie($attributes, $expectedStatus)
  {
    $this->realTest($attributes, $expectedStatus);
  }

  /**
   * provider returns values used to call $this->testMovie()
   * 
   * @return array ( ( attributes1, expectedStatus1 ), (attributes2, expectedStatus2), ( ... ) )
   */
  public function provider()
  {
    return array(
      // test all genres, favorite should queue all items
      array(
        array('genre_id'=>0),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      // test a single genre, both movies have this genre
      array(
        array('genre_id'=>1),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      // test a single genre, neither movie have this genre
      array(
        array('genre_id'=>4),
        array(
          'STATUS_NOMATCH'=>2,
          'STATUS_QUEUED'=>0,
      )),
      // test all feeds, favorite should queue all items
      array(
        array('feed_id'=>0),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      // test a single feed, one movie has a feedItem with this feed
      array(
        array('feed_id'=>2),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      // test minimum rating, movies have rating 50 and 90 so both queue
      array(
        array('rating'=>30),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      // test minimum = rating, both should match
      array(
        array('rating'=>50),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      // test minimum rating with exclude, only one should match
      array(
        array('rating'=>60),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      // test minimum rating with both movies excluded
      array(
        array('rating'=>95),
        array(
          'STATUS_NOMATCH'=>2,
          'STATUS_QUEUED'=>0,
      )),
      // test maximum year with a low value, fixtures have year 2000 and 2010 so no match
      array(
        array('maxYear'=>1950),
        array(
          'STATUS_NOMATCH'=>2,
          'STATUS_QUEUED'=>0,
      )),
      // test maximum  = year, should match one item
      array(
        array('maxYear'=>2000),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      // test maximum > year, should match one item
      array(
        array('maxYear'=>2001),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      // test maximum year, should match both
      array(
        array('maxYear'=>2015),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      // test minimum year, should match both
      array(
        array('minYear'=>1900),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      // test minimum year =, should match both
      array(
        array('minYear'=>2000),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      // test minimum year >, should match one
      array(
        array('minYear'=>2001),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      // test minmum year at high value, should match none
      array(
        array('minYear'=>2015),
        array(
          'STATUS_NOMATCH'=>2,
          'STATUS_QUEUED'=>0,
      )),
    );
  }

  /**
   * testMovieNoQuality is destructive to database state, so gets its own
   * function to enable resetting that data
   * 
   * @return void
   */
  public function testMovieNoQuality()
  {
    $fav = favoriteMovie::model()->findByPk(1);
    $ids = $fav->qualityIds;
    $this->realTest(
        array('qualityIds'=>array()),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        ),
        $fav
    );
    $fav->qualityIds = $ids;
    $fav->save();
  }
}
