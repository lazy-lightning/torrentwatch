<?php

require_once 'TVDB.php';

class TVDbTvShowAdapter extends ScraperAdapter
{
  private $tvShows;
  private $addGenreCommand;

  public $table = 'TVDbTvShowAdapter';
  public $modelClass = 'tvShow';

  /**
    * @var tvShow $tvShow
    * @var array $genre set of genre strings
    */
  protected function addGenres($tvShow, $genres)
  {
    $cmd = $this->createAddGenreCommand();
    $cmd->bindValue(':tvShow', $tvShow->id);
    foreach($genres as $genre)
    {
      $cmd->bindValue(':genre', $this->factory->genreByTitle($genre)->id)
          ->execute();
    }
  }

  protected function createAddGenreCommand()
  {
    if($this->addGenreCommand === null)
    {
      $this->addGenreCommand = Yii::app()->getDb()->createCommand(
          'INSERT INTO tvShow_genre (tvShow_id, genre_id) VALUES (:tvShow, :genre)'
      );
    }
    return $this->addGenreCommand;
  }

  public function createScanCommand($timeLimit, $columns = array())
  {
    return parent::createScanCommand($timeLimit, array_merge($columns, array('title')));
  }

  public function getScraper($resultRow)
  {
    if($resultRow['scraper_id'] !== null)
      $scraper = $this->getScraperById($resultRow['scraper_id']);
    else
      $scraper = $this->getScraperByTitle($resultRow['title']);
    if($scraper)
      $this->tvShows[$scraper->id] = $scraper;
    return $scraper;
  }

  public function getScraperById($scraperId)
  {
    if(!isset($this->tvShows[$scraperId]))
      $this->tvShows[$scraperId] = TV_Shows::findById($scraperId);
    return $this->tvShows[$scraperId];
  }

  public function getScraperByTitle($title)
  {
    if(($result = TV_Shows::search($title)))
      return $result[0];
    return false;
  }

  public function updateFromScraper($tvShow_id, $scraper)
  {
    $tvShow = tvShow::model()->findByPk($tvShow_id);
    if(!$tvShow instanceof tvShow)
      return false;
    if(!empty($scraper->seriesName))
      $tvShow->title = $scraper->seriesName;
    if(!empty($scraper->network))
      $tvShow->network_id = $this->factory->networkByTitle($scraper->network)->id;
    if(!empty($scraper->rating))
      $tvShow->rating = (integer) $scraper->rating;
    if(!empty($scraper->overview) && strlen($scraper->overview) > strlen($tvShow->description))
      $tvShow->description = $scraper->overview;
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

}
