<?php

class IMDbAdapter
{
  protected $factory;

  public function __construct($factory = null)
  {
    if($factory===null)
      $factory = Yii::app()->modelFactory;
    $this->factory = $factory;
  }

  public function getScraper($attribute, $accuracyLimit = 75)
  {
    // trimming 't' allows for url fragment from http://imdb.com/title/tt012345/
    $id=ltrim($attribute, 't');
    if(is_integer($id))
      return $this->getScraperByImdbId($id, $accuracyLimit);
    elseif(is_string($attribute))
      return $this->getScraperByTitle($attribute, $accuracyLimit);
    else
      throw new CException('Unknown attribute type passed to getScraper: '.gettype($attribute));
  }

  public function getScraperByImdbId($id, $accuracyLimit = 75)
  {
      $url = sprintf('http://www.imdb.com/title/tt%07d/', $row['imdbId']);
      $scraper = new IMDbScraper('', $url);

      if($scraper->accuracy < $accuracyLimit) {
        Yii::log("Failed scrape of id $id\n", CLogger::LEVEL_INFO);
        $scraper = false;
      }

      return $scraper;
  }

  public function getScraperByTitle($title, $accuracyLimit = 75)
  {
      if(substr($title, -4) === '1080')
        $title = substr($title, 0, -4);

      // replace . with space, imdb isn't a fan of this.movie.name.(2009)
      $title = str_replace('.', ' ', $title);
      $scraper = new IMDbScraper($title);

      // maybee it has a prefix
      if($scraper->accuracy < $accuracyLimit  &&
         false !== ($pos = strpos($title, '-')))
      {
        $scraper = new IMDbScraper(substr($title, $pos+1));
      }

      // maybee there are some bs numbers at the begining
      if($scraper->accuracy < $accuracyLimit &&
         $title !== ($tmpTitle = preg_replace('/^\d+\.?/', '', $title)))
      {
        $scraper = new IMDbScraper($tmpTitle);
      }
      if($scraper->accuracy < $accuracyLimit)
      {
        Yii::log("Failed scrape of $title\n", CLogger::LEVEL_INFO);
        $scraper = false;
      }

      return $scraper;
  }

  public function updateMovieFromScraper($movie, $scraper)
  {
    // imdb returns iso-8859-1
    $movie->year = $scraper->year;
    $movie->name = iconv('ISO-8859-1', Yii::app()->charset, $scraper->title);
    $movie->runtime = $scraper->runtime;
    $movie->plot = iconv('ISO-8859-1', Yii::app()->charset, $scraper->plot);
    $movie->rating = strtok($scraper->rating, '/');
    $movie->imdbId = $scraper->imdbId;
    $movie->lastImdbUpdate = time();
    if($movie->save()) {
      Yii::trace("Updated $scraper->title", 'application.commands.updateIMDbCommand');
      if(is_array($scraper->genres)) {
        foreach($scraper->genres as $genre) {
          $record = new movie_genre;
          $record->movie_id = $movie->id;
          $record->genre_id = $this->factory->genreByTitle($genre)->id;
          $record->save();
        }
      }
      return True;
    }
    
    Yii::log('Error saving movie after IMDB update.', CLogger::LEVEL_ERROR);
    return False;
  }

}

