<?php

class favoriteManagerTvShowFilterTest extends favoriteManagerFilterTest
{
  public $testClass = 'favoriteTvShow';

  public $fixtures = array(
      'favoriteTvShow'=>'favoriteTvShow',
      'tvShow'=>':tvShow',
      'tvEpisode'=>':tvEpisode',
      'feedItem'=>':feedItem',
      'feed'=>':feed',
      'dvrConfig'=>':dvrConfig',
  );

  public function testTvShowNoQuality()
  {
    $fav = favoriteTvShow::model()->findByPk(1);
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

  public function testTvShowMinSeasonLow()
  {
    $this->realTest(
        array('minSeason'=>1),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        )
    );
  }
  public function testTvShowMinSeasonMiddle()
  {
    $this->realTest(
        array('minSeason'=>2),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }

  public function testTvShowMinSeasonHigh()
  {
    $this->realTest(
        array('minSeason'=>3),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }

  public function testTvShowMinSeasonHigher()
  {
    $this->realTest(
        array('minSeason'=>4),
        array(
          'STATUS_NOMATCH'=>2,
          'STATUS_QUEUED'=>0,
        )
    );
  }

  public function testTvShowMaxSeasonLow()
  {
    $this->realTest(
        array('maxSeason'=>1),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }
  public function testTvShowMaxSeasonMiddle()
  {
    $this->realTest(
        array('maxSeason'=>2),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }
  public function testTvShowMaxSeasonHigh()
  {
    $this->realTest(
        array('maxSeason'=>3),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        )
    );
  }
  public function testTvShowMaxSeasonHigher()
  {
    $this->realTest(
        array('maxSeason'=>4),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        )
    );
  }

  public function testTvShowMinEpisodeLow()
  {
    $this->realTest(
        array('minEpisode'=>1),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        )
    );
  }
  public function testTvShowMinEpisodeMiddle()
  {
    $this->realTest(
        array('minEpisode'=>2),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }
  public function testTvShowMinEpisodeHigh()
  {
    $this->realTest(
        array('minEpisode'=>5),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }
  public function testTvShowMinEpisodeHigher()
  {
    $this->realTest(
        array('minEpisode'=>6),
        array(
          'STATUS_NOMATCH'=>2,
          'STATUS_QUEUED'=>0,
        )
    );
  }
  public function testTvShowMaxEpisodeLow()
  {
    $this->realTest(
        array('maxEpisode'=>1),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }
  public function testTvShowMaxEpisodeMiddle()
  {
    $this->realTest(
        array('maxEpisode'=>4),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
        )
    );
  }
  public function testTvShowMaxEpisodeHigh()
  {
    $this->realTest(
        array('maxEpisode'=>5),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        )
    );
  }
  public function testTvShowMaxEpisode()
  {
    $this->realTest(
        array('maxEpisode'=>6),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
        )
    );
  }

}
