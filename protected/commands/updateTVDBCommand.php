<?php

class updateTVDBCommand extends CConsoleCommand {

  // array of loaded tvShows indexed by tvdbid
  private $tvShows = array();

  public function run($args) {
    require_once('TVDB.php');
    $this->updateTvShows();
    $this->updateTvEpisodes();
  }

  protected function updateTvEpisodes() {
    $db = Yii::app()->db;
    $now=time();
    $scanned = array();
    $reader = $db->createCommand('SELECT e.id id, e.season season, e.episode episode, s.tvdbId tvdbId'.
                                 '  FROM tvShow s,'.
                                 '       ( SELECT * FROM tvEpisode e'.
                                 '          WHERE e.description IS NULL'.
                                 '            AND e.lastTvdbUpdate < '.($now-(3600*48)).
                                 '       ) e'.
                                 ' WHERE s.tvdbId NOT NULL'.
                                 '   AND s.id = e.tvShow_id'

    )->query();
    foreach($reader as $row) {
      $scanned[] = $row['id'];
      // Dont have the class functions written for date based episode
      if($row['episode'] > 1000) {
        continue;
      }

      echo "Looking for tvdbId ".$row['tvdbId']."\n";
      if(!isset($this->tvShows[$row['tvdbId']])) {
        $this->tvShows[$row['tvdbId']] = TV_Shows::findById($row['tvdbId']);
      }

      $tvdbShow = $this->tvShows[$row['tvdbId']];

      if(!$tvdbShow) {
        continue;
      }

      echo "Looking for episode ".$row['season']."x".$row['episode']."\n";
      $ep = $tvdbShow->getEpisode($row['season'], $row['episode']);
      if(!$ep) {
        continue;
      }

      echo "Found! Updating ".$ep->name."\n";
      $tvEpisode = tvEpisode::model()->findByPk($row['id']);
      $tvEpisode->firstAired = $ep->firstAired;
      $tvEpisode->description = $ep->overview;
      $tvEpisode->title = $ep->name;
      $tvEpisode->save();
    }
    tvEpisode::model()->updateByPk($scanned, array('lastTvdbUpdate'=>$now));
  }

  protected function updateTvShows() {
    $db = Yii::app()->db;
    $scanned = array();
    $now = time();

    $reader = $db->createCommand('SELECT id,title FROM tvShow'.
                                 ' WHERE description IS NULL'.
                                 '   AND lastTvdbUpdate < '.($now-(3600*48))
    )->query();
    foreach($reader as $row) {
      $scanned[]= $row['id'];
      echo "Searching for ".$row['title']."\n";
      $tvdbShows = TV_Shows::search($row['title']);
      if(!$tvdbShows) {
        continue;
      }
      $data = $tvdbShows[0];
      $this->tvShows[$data->tvdbId] = $data;

      echo "Found data for ".$data->seriesName."\n";
      $tvShow = tvShow::model()->findByPk($row['id']);
      // Dont change the title will mess up factory::tvShowByTitle()
      // perhaps create new column for tvdb Name ?
      //$tvShow->title = $data->seriesName;
      if(!empty($data->network))
        $tvShow->network_id = factory::networkByTitle($data->network)->id;
      $tvShow->rating = $data->rating;
      $tvShow->description = $data->overview;
      $tvShow->tvdbId = $data->id;

      if(empty($tvShow->title)) {
        Yii::log('Trying to save a tvShow with no title from tvdb update'."\n".print_r(debug_backtrace()), CLogger::LEVEL_ERROR);
      }
      elseif(!$tvShow->save()) {
        Yii::log('Error saving tvShow from tvdb update', CLogger::LEVEL_ERROR);
        continue;
      }

      if(!empty($data->genres)) {
        // Initialize our SQL INSERT command
        $genre_id = '';
        if(!isset($addGenre)) {
          $addGenre = $db->createCommand(
              "INSERT INTO tvShow_genre (tvShow_id, genre_id) VALUES (:tvShow, :genre);"
          );
          $addGenre->bindParam(':genre', $genre_id);
        }
        $addGenre->bindValue(':tvShow', $tvShow->id);

        // Loopthrough the genres linking them all to the tvShow
        foreach($data->genres as $genre) {
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
    tvShow::model()->updateByPk($scanned, array('lastTvdbUpdate'=>$now));
  }
}
