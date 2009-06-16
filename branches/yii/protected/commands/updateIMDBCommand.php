<?php

class updateIMDbCommand extends CConsoleCommand {

  public function run($args) {
    $this->updateMovies();
    // EXPERIMENTAL
    $this->updateOthers();
  }

  protected function updateOthers() {
    $db = Yii::app()->db;
    $scanned = array();
    $reader = $db->createCommand('SELECT id, title'.
                                 '  FROM other'.
                                 ' WHERE lastImdbUpdate = 0'
    )->query();
    foreach($reader as $row) {

      $title = $row['title'];
      if(substr($title, -4) === '1080')
        $title = substr($title, 0, -4);

      echo "Searching IMDb for $title\n";
      $scraper = new IMDbScraper($title);

      if($scraper->accuracy < 75) {
        $scanned[] = $row['id'];
        echo "Failed scrape\n";
        continue;
      }

      echo "Found! Updating to ".$scraper->title."\n";

      $movie = factory::movieByImdbId($scraper->imdbId, $row['title']);

      feedItem::model()->updateAll(
          array('other_id'=>NULL,
                'movie_id'=>$movie->id,
          ),
          'other_id = '.$row['id']
      );
      other::model()->deleteByPk($row['id']);
      $this->updateMovieFromScraper($movie, $scraper);
    }
    other::model()->updateByPk($scanned, array('lastImdbUpdate'=>time()));
  }

  protected function updateMovies() {
    $db = Yii::app()->db;
    $now = time();
    $scanned = array();
    $reader = $db->createCommand('SELECT id, imdbId'.
                                 '  FROM movie'.
                                 ' WHERE lastImdbUpdate <'.($now-(3600*24)). // one update per 24hrs
                                 '   AND rating IS NULL;'

    )->query();
    foreach($reader as $row) {
      echo "FOO\n";
      $scanned[] = $row['id'];

      echo "Looking for Imdb Id: ".$row['imdbId']."\n";
      $url = sprintf('http://www.imdb.com/title/tt%07d/', $row['imdbId']);
      $scraper = new IMDbScraper('', $url);

      if($scraper->accuracy < 75) {
        echo "Failed scrape\n";
        continue;
      }

      echo "Found! Updating ".$scraper->title."\n";

      $this->updateMovieFromScraper($row['id'], $scraper);
    }
    movie::model()->updateByPk($scanned, array('lastImdbUpdate'=>$now));
  }

  protected function updateMovieFromScraper($movie, $scraper)
  {
    if(!is_object($movie))
      $movie = movie::model()->findByPk($movie);

    $movie->year = $scraper->year;
    $movie->name = $scraper->title;
    $movie->runtime = $scraper->runtime;
    $movie->plot = $scraper->plot;
    $movie->rating = strtok($scraper->rating, '/');
    if($movie->save()) {
      if(is_array($scraper->genres)) {
        foreach($scraper->genres as $genre) {
          $record = new movie_genre;
          $record->movie_id = $movie->id;
          $record->genre_id = factory::genreByTitle($genre)->id;
          $record->save();
        }
      }
      return True;
    }
    return False;
  }
}

