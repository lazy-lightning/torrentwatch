<?php

class IMDbMovieAdapter extends ScraperAdapter
{
  public $table = 'IMDbMovieAdapter';
  public $modelClass = 'movie';

  public $accuracyLimit = '75';

  public function createScanCommand($timeLimit, $attributes=array())
  {
    return parent::createScanCommand($timeLimit, array_merge($attributes, array('title')));
  }

  public function getScraper($resultRow)
  {
   // trimming 't' allows for url fragment from http://imdb.com/title/tt012345/
    $id=ltrim($resultRow['scraper_id'], 't');
    if(is_numeric($id))
      return $this->getScraperById($id);
    else
      return $this->getScraperByTitle($resultRow['title']);
  }

  public function getScraperById($resultRow)
  {
    $url = sprintf('http://www.imdb.com/title/tt%07d/', $resultRow[$this->getScraperColumn()]);
    $scraper = new IMDbScraper('', $url, $this->accuracyLimit);

    if($scraper->accuracy < $this->accuracyLimit)
    {
      Yii::log("Failed scrape of id $id\n", CLogger::LEVEL_INFO, 'application.components.IMDbAdapter');
      $scraper = false;
    }

    return $scraper;
  }

  public function getScraperByTitle($title)
  {
    if(substr($title, -4) === '1080')
      $title = substr($title, 0, -4);

    // replace . with space, imdb isn't a fan of this.movie.name.(2009)
    $title = str_replace('.', ' ', $title);
    $scraper = new IMDbScraper($title);

    // maybee it has a prefix
    if($scraper->accuracy < $this->accuracyLimit  &&
       false !== ($pos = strpos($title, '-')))
    {
      $scraper = new IMDbScraper(substr($title, $pos+1));
    }

    // maybee there are some bs numbers at the begining
    if($scraper->accuracy < $this->accuracyLimit &&
       $title !== ($tmpTitle = preg_replace('/^\d+\.?/', '', $title)))
    {
      $scraper = new IMDbScraper($tmpTitle);
    }
    if($scraper->accuracy < $this->accuracyLimit)
    {
      Yii::log("Failed scrape of $title\n", CLogger::LEVEL_INFO, 'application.components.IMDbAdapter');
      $scraper = false;
    }

    return $scraper;
  }

  public function updateFromScraper($model_id, $scraper)
  {
    if(is_object($model_id))
      $movie = $model_id;
    else
      $movie = CActiveRecord::model($this->modelClass)->findByPk($model_id);
    if(!$movie instanceof $this->modelClass)
      return false;
     // imdb returns iso-8859-1
    $charset = 'ISO-8859-1';
    if(!empty($scraper->year))
      $movie->year = $scraper->year;
    if(!empty($scraper->title))
      $movie->name = iconv($charset, Yii::app()->charset, $scraper->title);
    if(!empty($scraper->runtime))
      $movie->runtime = $scraper->runtime;
    $plot = iconv($charset, Yii::app()->charset, $scraper->plot);
    if(!empty($plot) && strlen($plot) > strlen($movie->plot))
      $movie->plot = $plot;
    if(!empty($scraper->rating))
      $movie->rating = strtok($scraper->rating, '/');

    if($movie->save())
    {
      Yii::trace("Updated $scraper->title", 'application.components.IMDbAdapter');
      // TODO: the following logic should be moved into the movie class, or a genres behavior
      if(is_array($scraper->genres))
      {
        foreach($scraper->genres as $genre)
        {
          $record = new movie_genre;
          $record->movie_id = $movie->id;
          $record->genre_id = $this->factory->genreByTitle($genre)->id;
          $record->save();
        }
      }
      return True;
    }

    Yii::log('Error saving movie after IMDB update.', CLogger::LEVEL_ERROR, 'application.components.IMDbAdapter');
    Yii::log(print_r($movie->getErrors(), true), CLogger::LEVEL_ERROR, 'application.components.IMDbAdapter');
    return False;

  }

}
