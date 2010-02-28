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

  /**
   * testTvShowFilter 
   * 
   * @dataProvider provider
   * @return void
   */
  public function testTvShowFilter($attributes, $expectedStatus)
  {
    $this->realTest($attributes, $expectedStatus);
  }

  /**
   * provider 
   * 
   * @return array
   */
  public function provider()
  {
    return array(
      array(
        array('minSeason'=>1),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      array(
        array('minSeason'=>2),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      array(
        array('minSeason'=>3),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      array(
        array('minSeason'=>4),
        array(
          'STATUS_NOMATCH'=>2,
          'STATUS_QUEUED'=>0,
      )),
      array(
        array('maxSeason'=>1),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      array(
        array('maxSeason'=>2),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      array(
        array('maxSeason'=>3),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      array(
        array('maxSeason'=>4),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      array(
        array('minEpisode'=>1),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      array(
        array('minEpisode'=>2),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      array(
        array('minEpisode'=>5),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      array(
        array('minEpisode'=>6),
        array(
          'STATUS_NOMATCH'=>2,
          'STATUS_QUEUED'=>0,
      )),
      array(
        array('maxEpisode'=>1),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      array(
        array('maxEpisode'=>4),
        array(
          'STATUS_NOMATCH'=>1,
          'STATUS_QUEUED'=>1,
      )),
      array(
        array('maxEpisode'=>5),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
      array(
        array('maxEpisode'=>6),
        array(
          'STATUS_NOMATCH'=>0,
          'STATUS_QUEUED'=>2,
      )),
    );
  }

  /**
   * testMovieNoQuality is destructive to database state, so gets its own
   * function to enable resetting that data
   * 
   * @return void
   */
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
    $fav->qualityIds = $ids;
    $fav->save();
  }

}
