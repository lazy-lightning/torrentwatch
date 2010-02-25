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

  public function testMovieAllFeeds()
  {
    $this->realTest(
        array('feed_id'=>0),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        )
    );
  }

  public function testMovieSpecificFeed()
  {
    $this->realTest(
        array('feed_id'=>2),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }

  public function testMovieLowRating()
  {
    $this->realTest(
        array('rating'=>30),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        )
    );
  }
  public function testMovieEvenRating()
  {
    $this->realTest(
        array('rating'=>50),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        )
    );
  }
  public function testMovieHighRating()
  {
    $this->realTest(
        array('rating'=>60),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }
  public function testMovieHighestRating()
  {
    $this->realTest(
        array('rating'=>100),
        array(
          'STATUS_NOMATCH'=>2,
          'STATUS_QUEUED'=>0,
        )
    );
  }

  public function testMovieMaxYearLow()
  {
    $this->realTest(
        array('maxYear'=>1950),
        array(
          'STATUS_NOMATCH'=>2,
          'STATUS_QUEUED'=>0,
        )
    );
  }
  public function testMovieMaxYearEven()
  {
    $this->realTest(
        array('maxYear'=>2000),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }
  public function testMovieMaxYearHigh()
  {
    $this->realTest(
        array('maxYear'=>2001),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }
  public function testMovieMaxYearHighest()
  {
    $this->realTest(
        array('maxYear'=>2015),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        )
    );
  }
  public function testMovieMinYearLow()
  {
    $this->realTest(
        array('minYear'=>1900),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        )
    );
  }
  public function testMovieMinYearEven()
  {
    $this->realTest(
        array('minYear'=>2000),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        )
    );
  }
  public function testMovieMinYearHigh()
  {
    $this->realTest(
        array('minYear'=>2001),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }
  public function testMovieMinYearHighest()
  {
    $this->realTest(
        array('minYear'=>2015),
        array(
          'STATUS_NOMATCH'=>2,
          'STATUS_QUEUED'=>0,
        )
    );
  }

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
    // above operation was destructive to future tests, so reset that data
    $fav->qualityIds = $ids;
    $fav->save();
  }
}
