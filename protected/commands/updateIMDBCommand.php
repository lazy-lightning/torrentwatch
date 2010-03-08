<?php

class updateIMDbCommand extends BaseConsoleCommand {

  /**
   * in the future the could be replaced with an adapter for themoviedb
   * 
   * @var IMDbAdapter
   */
  private $movieDetails;

  private $db;

  /**
   * factory 
   * 
   * @var modelFactory
   */
  protected $factory;

  /**
   * toSave 
   * 
   * @var array multidimensional array containing values usable by updateDatabase()
   */
  protected $toSave = array();

  /**
   * scanned is a multidimensional array.  The first element of each row
   * is a CActiveRecord object.  The second element of same row is an array of
   * primary keys to be updated
   * 
   * @var array ( # => ( CActiveRecord, primaryKeys ) )
   */
  protected $scanned = array();

  public function run($args) {
    $this->db = Yii::app()->db;
    $this->factory = Yii::app()->modelFactory;
    $this->movieDetails = new IMDbAdapter;

    $this->scanMovies();
    $this->scanOthers();

    $transaction = $this->db->beginTransaction();
    try {
      $this->updateDatabase();
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
  }

  protected function repointOther($otherId, $movieId)
  {
    feedItem::model()->updateAll(
        array(
          'other_id'=>NULL,
          'movie_id'=>$movieId,
        ),
        'other_id = '.$otherId
    );
    // delete the model, could just let dbMaintinance take care of it
    other::model()->deleteByPk($otherId);
  }

  protected function scanMovies() {
    $scanned = array();
    $reader = $this->db->createCommand(
        'SELECT id, imdbId'.
        '  FROM movie'.
        ' WHERE lastImdbUpdate <'.(time()-(3600*48)). // one update per 48hrs
        '   AND imdbId IS NOT NULL'.
        '   AND rating IS NULL;'

    )->queryAll();
    foreach($reader as $row) {
      $scanned[] = $row['id'];
      
      echo "Looking for Imdb Id: ".$row['imdbId']."\n";
      if(($scraper = $this->movieDetails->getScraper($row['imdbId'])))
      {
        echo "Found ".$scraper->title."\n";
        $this->toSave[] = array(
            'scraper'=>$scraper,
            'movie_id'=>$row['id']
        );
      }
    }

    if(count($scanned))
      $this->scanned[] = array(movie::model(), $scanned);
  }

  protected function scanOthers() {
    $scanned = array();
    $reader = $this->db->createCommand(
        'SELECT id, title, lastUpdated'.
        '  FROM other'.
        ' WHERE lastImdbUpdate = 0'
    )->queryAll();
    foreach($reader as $row) {
      $title = $row['title'];
      echo "Searching IMDb for $title\n";
      if(($scraper = $this->movieDetails->getScraper($title)))
      {
        echo "Found ".$scraper->title."\n";
        $this->toSave[] = array(
            'other_id'    => $row['id'], 
            'other_title' => $row['title'],
            'other_lastUpdated' => $row['lastUpdated'],
            'scraper'     => $scraper
        );
      }
      else
        $scanned[] = $row['id'];
    }

    if(count($scanned))
      $this->scanned[] = array(other::model(), $scanned);
  }

  /**
   * prepareOther gets the movie 
   * 
   * @param array $row 
   * @return movie
   */
  protected function prepareOther($row)
  {
    $movie = $this->factory->movieByImdbId($row['scraper']->imdbId, $row['other_title']);
    $movie->lastUpdated = max($movie->lastUpdated, $row['other_lastUpdated']);
    // repoint feed items
    $this->repointOther($row['other_id'], $movie->id);
    // fix bug where newly saved CActiveRecords cant be saved again
    $movie->setPrimaryKey($movie->id);
    return $movie;
  }

  protected function updateDatabase()
  {
    $now = time();
    foreach($this->scanned as $row)
    {
      list($model, $scanned) = $row;
      $model->updateByPk($scanned, array('lastImdbUpdate'=>$now));
    }
    $this->scanned = array();

    foreach($this->toSave as $row) {
      if(isset($row['other_id']))
        $movie = $this->prepareOther($row);
      else
        $movie = movie::model()->findByPk($row['movie_id']);

      $this->movieDetails->updateMovieFromScraper($movie, $row['scraper']);
    }
    echo 'Saved '.count($this->toSave).' items'."\n";
    $this->toSave = array();
  }

}

