<?php

class feedItemTest extends DbTestCase
{
  protected $fixtures = array(
      'feedItems'=>'feedItem',
      'tvEpisodes'=>'tvEpisode',
      'favoriteMovies'=>'favoriteMovie',
      'favoriteMovies_quality'=>':favoriteMovies_quality',
      'favoriteTvShows'=>'favoriteTvShow',
      'favoriteTvShows_quality'=>':favoriteTvShows_quality',
      'feedItems_quality'=>':feedItem_quality',
      'movie'=>'movie',
      'movie_genre'=>'movie_genre',
      'tvShow'=>':tvShow',
      'dvrConfig'=>':dvrConfig',
  );

  public function testGetFavoriteTvShow()
  {
    $favorite = feedItem::model()->findByPk(1)->getFavorite();
    $this->assertNotEquals(false, $favorite);
    $this->assertType('favoriteTvShow', $favorite);
    $this->assertEquals($this->favoriteTvShows['first']['id'], $favorite->id);
  }

  public function testGetFavoriteMovie()
  {
    $favorite = feedItem::model()->findByPk(1)->getFavorite();
    $this->assertNotEquals(false, $favorite);
    $this->assertType('favoriteMovie', $favorite);
    $this->assertEquals($this->favoriteMovies['first']['id'], $favorite->id);
  }
}
