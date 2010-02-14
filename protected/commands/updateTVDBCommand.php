<?php

class updateTVDBCommand extends BaseConsoleCommand {

  // array of loaded tvShows indexed by tvdbid
  private $tvShows = array();
  protected $factory;

  public function run($args) {
    require_once('TVDB.php');
    $this->factory = Yii::app()->modelFactory;
    $this->updateTvShows();
    $this->updateTvEpisodes();
  }

  protected function updateTvEpisodes() {
    $db = Yii::app()->db;
    $now=time();
    $scanned = $toSave = array();
    $reader = $db->createCommand('SELECT e.id id, e.season season, e.episode episode, s.tvdbId tvdbId'.
                                 '  FROM tvShow s,'.
                                 '       ( SELECT * FROM tvEpisode e'.
                                 '          WHERE e.description IS NULL'.
                                 '            AND e.lastTvdbUpdate < '.($now-(3600*48)).
                                 '       ) e'.
                                 ' WHERE s.tvdbId NOT NULL'.
                                 '   AND s.id = e.tvShow_id'

    )->queryAll();
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
      $toSave[] = $tvEpisode;
    }
    $transaction = Yii::app()->db->beginTransaction();
    try {
      echo count($toSave)." tv Episodes to save\n";
      foreach($toSave as $record)
        $record->save();
      if(count($scanned))
        tvEpisode::model()->updateByPk($scanned, array('lastTvdbUpdate'=>$now));
      $transaction->commit();
    } catch ( Exception $e ) {
      $transaction->rollback();
    }
  }

  protected function updateTvShows() {
    $db = Yii::app()->db;
    $scanned = $toSave = array();
    $now = time();

    $reader = $db->createCommand('SELECT id,title FROM tvShow'.
                                 ' WHERE description IS NULL'.
                                 '   AND lastTvdbUpdate < '.($now-(3600*48))
    )->queryAll();
    foreach($reader as $row) 
    {
      $scanned[]= $row['id'];
      echo "Searching for ".$row['title']."\n";
      $tvdbShows = TV_Shows::search($row['title']);
      if(!$tvdbShows)
        continue;

      $data = $tvdbShows[0];
      echo "Found data for ".$data->seriesName."\n";
      $this->tvShows[$data->tvdbId] = $data;

      $toSave[$row['id']] = $data;
    }

    echo count($toSave)." tv Shows to save\n";
    $transaction = $db->beginTransaction();
    try {
      foreach($toSave as $id => $data)
      {
        $tvShow = tvShow::model()->findByPk($id);
        // Dont change the title will mess up $this->factory->tvShowByTitle()
        // perhaps create new column for tvdb Name ?
        //$tvShow->title = $data->seriesName;

        if(!empty($data->network))
          $tvShow->network_id = $this->factory->networkByTitle($data->network)->id;
        $tvShow->rating = (integer) $data->rating;
        $tvShow->description = $data->overview;
        $tvShow->tvdbId = $data->id;
  
        if($tvShow->save()) 
        {
          echo "Saved {$tvShow->title}\n";
          if(!empty($data->genres)) 
          {
            // Initialize our SQL INSERT command
            $genre_id = '';
            if(!isset($addGenre)) 
            {
              $addGenre = $db->createCommand(
                  "INSERT INTO tvShow_genre (tvShow_id, genre_id) VALUES (:tvShow, :genre);"
              );
              $addGenre->bindParam(':genre', $genre_id);
            }
            $addGenre->bindValue(':tvShow', $tvShow->id);
    
            // Loopthrough the genres linking them all to the tvShow
            foreach($data->genres as $genre) 
            {
              $genre_id = $this->factory->genreByTitle($genre)->id;
              $addGenre->execute();
            }
          }
        } 
        else 
        {
          echo "Failed save {$tvShow->title}\n";
          Yii::log('Error saving tvShow after tvdb update', CLogger::LEVEL_ERROR);
          Yii::log(print_r($tvShow->errors, TRUE));
        }
      }
      if(count($scanned))
        tvShow::model()->updateByPk($scanned, array('lastTvdbUpdate'=>$now));
      $transaction->commit();
    } catch ( Exception $e ) {
      $transaction->rollback();
      throw $e;
    }
  }
}

