<?php

class IMDbOtherAdapter extends IMDbMovieAdapter
{
  public $table = 'IMDbOtherAdapter';
  public $modelClass = 'other';

  protected function repointOther($other, $movie)
  {
    feedItem::model()->updateAll(
        array(
          'other_id'=>NULL,
          'movie_id'=>$movie->id,
        ),
        'other_id = '.$other->id
    );
    $other->delete();
  }

  public function updateFromScraper($model_id, $scraper)
  {
    $other = other::model()->findByPk($model_id);
    $movie = $this->factory->movieByImdbId($scraper->getId(), $scraper->getName());
    if($other->lastUpdated > $movie->lastUpdated)
      $movie->lastUpdated = $other->lastUpdated;
    $this->repointOther($other, $movie);
    // fix a yii bug, fixed upstream in svn
    $movie->setPrimaryKey($movie->id);
    return parent::updateFromScraper($movie, $scraper);
  }
}
