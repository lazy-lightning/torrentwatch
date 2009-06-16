<?php

class updateIMDbCommand extends CConsoleCommand {

  public function run($args) {
    require_once('TVDB.php');
    $this->updateMovies();
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
      $scanned[] = $row['id'];

      echo "Looking for Imdb Id: ".$row['imdbId']."\n";
      $url = sprintf('http://www.imdb.com/title/tt%07d/', $row['imdbId']);
      $scraper = new IMDbFetch($url);

      if(!$scraper->success) {
        echo "Failed scrape\n";
        continue;
      }

      echo "Found! Updating ".$scraper->name."\n";
      $movie = movie::model()->findByPk($row['id']);
      $movie->year = $scraper->year;
      $movie->name = $scraper->name;
      $movie->runtime = $scraper->runtime;
      $movie->plot = $scraper->plot;
      $movie->rating = strtok($scraper->rating, '/');
      if($movie->save()) {
        $genres = explode('|', $scraper->genre);
        // Initialize our SQL INSERT command
        $genre_id = '';
        if(!isset($addGenre)) {
          $addGenre = $db->createCommand(
              "INSERT INTO movie_genre (movie_id, genre_id) VALUES (:movie, :genre);"
          );
          $addGenre->bindParam(':genre', $genre_id);
        }
        $addGenre->bindValue(':movie', $movie->id);

        // Loopthrough the genres linking them all to the tvShow
        foreach($genres as $genre) {
          $genre = trim($genre);
          if(substr($genre, -4) == 'more')
            $genre = substr($genre, 0, -5);
          $g = factory::genreByTitle($genre);
          $genre_id = $g->id;
          if(is_numeric($genre_id)) {
            $addGenre->execute();
          } else {
            echo "Failure with loadGenre\n";
            var_dump($g);
          }
        }
      }
    }
    movie::model()->updateByPk($scanned, array('lastImdbUpdate'=>$now));
  }
}

