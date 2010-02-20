<?php

class favoriteManagerTvShowTest extends DbTestCase
{
  public $fixtures = array(
      'favoriteTvShow'=>'favoriteTvShow',
      'favoriteTvShows_quality'=>':favoriteTvShows_quality',
      'tvShow'=>':tvShow',
      'tvEpisode'=>':tvEpisode',
      'feedItem'=>':feedItem',
      'feedItem_quality'=>':feedItem_quality',
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

  public function testTvShowCheckFavoritesStatus()
  {
    // should match no items with STATUS_NEW
    Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NEW, false);

    $this->assertEquals(2, feedItem::model()->count('status = '.feedItem::STATUS_NOMATCH));
    $this->assertEquals(0, feedItem::model()->count('status = '.feedItem::STATUS_QUEUED));
  }

  public function testTvShowTimeLimitDisabled()
  {
    // should match both items with no limit
    Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NOMATCH, false);

    $this->assertEquals(0, feedItem::model()->count('status = '.feedItem::STATUS_NOMATCH));
    $this->assertEquals(2, feedItem::model()->count('status = '.feedItem::STATUS_QUEUED));
  }

  public function testTvShowTimeLimitLong()
  {
    // should match both items with 24 hours limit
    Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NOMATCH, 24*60*60);

    $this->assertEquals(0, feedItem::model()->count('status = '.feedItem::STATUS_NOMATCH));
    $this->assertEquals(2, feedItem::model()->count('status = '.feedItem::STATUS_QUEUED));
  }

  public function testTvShowTimeLimitShort()
  {
    // should match one item with 1 hour limit
    Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NOMATCH, 60*60);

    $this->assertEquals(1, feedItem::model()->count('status = '.feedItem::STATUS_NOMATCH));
    $this->assertEquals(1, feedItem::model()->count('status = '.feedItem::STATUS_QUEUED));
  }

  public function testTvShowTimeLimitDefault()
  {
    // should match one item with 1 hour limit set in dvrConfig
    Yii::app()->dvrConfig->matchingTimeLimit = 1;
    Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NOMATCH);

    $this->assertEquals(1, feedItem::model()->count('status = '.feedItem::STATUS_NOMATCH));
    $this->assertEquals(1, feedItem::model()->count('status = '.feedItem::STATUS_QUEUED));
  }
}

