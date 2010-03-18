<?php

define('PHPTVDB_API_KEY', 'BF9ED8F840277890');
  
  
//Include our files and we're done!
require_once 'TVDB/TVDB.class.php';
require_once 'TVDB/TV_Show.class.php';
require_once 'TVDB/TV_Shows.class.php';
require_once 'TVDB/TV_Episode.class.php';

class TVDbAdapter {

  protected $tvShows = array();

  /**
    * @var CDbCommand
    */
  private $addGenreCommand = false;

  /**
    * @var modelFactory
    */
  protected $factory;

  /**
    * @var modelFactory $factory
    */
  public function __construct($factory = null)
  {
    if($factory === null)
      $factory = Yii::app()->getComponent('modelFactory');
    $this->factory = $factory;
  }

  /**
    * @var tvShow $tvShow
    * @var array $genre set of genre strings
    */
  protected function addGenres($tvShow, $genres)
  {
    $cmd = $this->getAddGenreCommand();
    $cmd->bindValue(':tvShow', $tvShow->id);
    foreach($genres as $genre)
    {
      $cmd->bindValue(':genre', $this->factory->genreByTitle($genre)->id)
          ->execute();
    }
  }

  protected function getAddGenreCommand()
  {
    if($this->addGenreCommand === false)
    {
      $this->addGenreCommand = Yii::app()->getDb()->createCommand(
          'INSERT INTO tvShow_genre (tvShow_id, genre_id) VALUES (:tvShow, :genre)'
      );
    }
    return $this->addGenreCommand;
  }

  public function getTvShowScraperByTitle($title)
  {
    if(($result = TV_Shows::search($title)))
    {
      $result = $result[0];
      $this->tvShows[$result->tvdbId] = $result;
      return $result;
    }
    return false;
  }

  public function getTvEpisodeScraper($tvShowTvdbId, $season, $episode)
  {
    if(!($tvdbShow = $this->getTvShowScraperById($tvShowTvdbId)))
      return false;
    return $tvdbShow->getEpisode($season, $episode);
  }

  protected function getTvShowScraperById($id)
  {
    if(!isset($this->tvShows[$id]))
      $this->tvShows[$id] = TV_Shows::findById($id);
    return $this->tvShows[$id];
  }

  public function updateTvEpisodeFromScraper($tvEpisode, $scraper)
  {
    $tvEpisode->firstAired = $scaper->firstAired;
    $tvEpisode->description = $scaper->overview;
    $tvEpisode->title = $scaper->name;
    if($tvEpisode->save() === false)
    {
      $this->logError($tvEpisode);
      return false;
    }
    return true;
  }

  public function updateTvShowFromScraper($tvShow, $scraper)
  {
    if(!empty($scraper->network))
      $tvShow->network_id = $this->factory->networkByTitle($scraper->network)->id;
    $tvShow->rating = (integer) $scraper->rating;
    $tvShow->description = $scraper->overview;
    $tvShow->tvdbId = $scraper->id;
    if($tvShow->save())
    {
      if(!empty($scraper->genres))
        $this->addGenres($tvShow, $scraper->genres);
    }
    else
    {
      $this->logError($tvShow);
      return false;
    }
    return true;
  }

  private function logError($model)
  {
    Yii::log("Error saving ".get_class($model)." after update", CLogger::LEVEL_ERROR, 'application.components.TVDbAdapter');
    Yii::log(print_r($model->errors, true), CLogger::LEVEL_ERROR, 'application.components.TVDbAdapter');
  }
}
