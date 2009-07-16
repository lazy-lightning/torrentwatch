<?php

class updateIMDbCommand extends BaseConsoleCommand {

  public function run($args) {
    $this->updateMovies();
    // EXPERIMENTAL
    $this->updateOthers();
  }

  protected function updateOthers() {
    $db = Yii::app()->db;
    $scanned = $toSave = array();
    $reader = $db->createCommand('SELECT id, title'.
                                 '  FROM other'.
                                 ' WHERE lastImdbUpdate = 0'
    )->query();
    foreach($reader as $row) {
      $scanned[] = $row['id'];
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

      $toSave[] = array($row['id'], $row['title'], $movie, $scraper);
    }

    $transaction = Yii::app()->db->beginTransaction();
    try {
      foreach($toSave as $arr) {
        list($id, $title, $scraper) = $arr;

        $movie = factory::movieByImdbId($scraper->imdbId, $title);

        feedItem::model()->updateAll(
            array('other_id'=>NULL,
                  'movie_id'=>$movie->id,
            ),
            'other_id = '.$id
        );
        other::model()->deleteByPk($id);
        $this->updateMovieFromScraper($movie, $scraper);
      }
      other::model()->updateByPk($scanned, array('lastImdbUpdate'=>time()));
      $transaction->commit();
    } catch ( Exception $e ) {
      $transaction->rollback();
      throw $e;
    }
  }

  protected function updateMovies() {
    $db = Yii::app()->db;
    $now = time();
    $scanned = $toSave = array();
    $reader = $db->createCommand('SELECT id, imdbId'.
                                 '  FROM movie'.
                                 ' WHERE lastImdbUpdate <'.($now-(3600*24)). // one update per 24hrs
                                 '   AND rating IS NULL;'

    )->query();
    foreach($reader as $row) {
      $scanned[] = $row['id'];

      echo "Looking for Imdb Id: ".$row['imdbId']."\n";
      $url = sprintf('http://www.imdb.com/title/tt%07d/', $row['imdbId']);
      $scraper = new IMDbScraper('', $url);

      if($scraper->accuracy < 75) {
        echo "Failed scrape\n";
        continue;
      }

      echo "Found! Updating ".$scraper->title."\n";
      $toSave[$row['id']] = $scraper;
    }

    $transaction = Yii::app()->db->beginTransaction();
    try {
      foreach($toSave as $id => $scraper)
        $this->updateMovieFromScraper($id, $scraper);

      movie::model()->updateByPk($scanned, array('lastImdbUpdate'=>$now));
      $transaction->commit();
    } catch ( Exception $e) {
      $transaction->rollback();
      throw $e;
    }
  }

  protected function updateMovieFromScraper($movie, $scraper)
  {
    if(!is_a($movie, 'movie'))
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
    } else
      Yii::log('Error saving movie after IMDB update.', CLogger::LEVEL_ERROR);

    return False;
  }
}

