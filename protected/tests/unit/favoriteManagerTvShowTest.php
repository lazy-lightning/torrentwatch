<?php

class favoriteManagerTvShowTest extends DbTestCase
{
  public $fixtures = array(
      'favoriteTvShow'=>'favoriteTvShow',
      'tvShow'=>':tvShow',
      'tvEpisode'=>':tvEpisode',
      'feedItem'=>':feedItem',
      'feed'=>':feed',
      'dvrConfig'=>':dvrConfig',
  );

  public function testTvShowCheckFavorite()
  {
    $fav = favoriteTvShow::model()->findByPk(1);
    // should match both items with default checkFavorite settings
    $this->assertType('favoriteTvShow', $fav);
    Yii::app()->dlManager->checkFavorite($fav);

    $this->assertEquals(0, feedItem::model()->count('status = '.feedItem::STATUS_NOMATCH));
    $this->assertEquals(2, feedItem::model()->count('status = '.feedItem::STATUS_QUEUED));
  }

  /**
   * testTimeLimit 
   * 
   * @dataProvider timeLimitProvider
   * @return void
   */
  public function testTimeLimit($limit, $status, $nomatch, $queued)
  {
    Yii::app()->dlManager->checkFavorites($status, $limit);

    $this->assertEquals($nomatch, feedItem::model()->count('status = '.feedItem::STATUS_NOMATCH));
    $this->assertEquals($queued, feedItem::model()->count('status = '.feedItem::STATUS_QUEUED));
  }

  /**
   * testTimeLimitInDvrConfig 
   * 
   * @dataProvider timeLimitProvider
   * @return void
   */
  public function testTimeLimitInDvrConfig($limit, $status, $nomatch, $queued)
  {
    if($limit === false)
      $limit = 0;
    else
      $limit = $limit/60/60;

    $old = Yii::app()->dvrConfig->matchingTimeLimit;
    Yii::app()->dvrConfig->matchingTimeLimit = $limit;
    Yii::app()->dlManager->checkFavorites($status);

    $nomatchCount = feedItem::model()->count('status = '.feedItem::STATUS_NOMATCH);
    $queuedCount = feedItem::model()->count('status = '.feedItem::STATUS_QUEUED);
    // previous bit was destructive to future tests, so reset it before any assert()
    Yii::app()->dvrConfig->matchingTimeLimit = $old;

    $this->assertEquals($nomatch, $nomatchCount, 'nomatch count');
    $this->assertEquals($queued, $queuedCount, 'queued count');
  }

  public function timeLimitProvider()
  {
    return array(
        //  timelimit    status               nomatch   queued
        // should match no items with STATUS_NEW
        array(false,    feedItem::STATUS_NEW,       2,    0),
        // should match both items with no limit
        array(false,    feedItem::STATUS_NOMATCH,   0,    2),
        // should match both items with 24 hours limit
        array(24*60*60, feedItem::STATUS_NOMATCH,   0,    2),
        // should match one item with 1 hour limit
        array(60*60,    feedItem::STATUS_NOMATCH,   1,    1),
    );
  }
}

