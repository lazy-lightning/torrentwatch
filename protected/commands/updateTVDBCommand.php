<?php

class updateTVDBCommand extends BaseConsoleCommand {

  /**
    * @var TVDbAdapter
    */
  protected $details;

  /**
    * @var modelFactory
    */
  protected $factory;

  /**
   * toSave 
   * 
   * @var array
   */
  protected $toSave = array();

  /**
   * scanned 
   * 
   * @var array
   */
  private $scanned = array();

  /**
   * tvShowRescanTime 
   * 
   * @var integer the time in secconds to wait before rescanning tvdb for data when no info is found
   *              default of 172800 makes for 48 hours.
   */
  public $tvShowRescanTime = 172800;

  /**
   * run 
   * 
   * @param mixed $args 
   * @return void
   */
  public function run($args) {
    $this->factory = Yii::app()->modelFactory;
    $this->details = new TVDbAdapter($this->factory);

    $toScan = array(
        'tvShow' => array(
          'command' => $this->getScanTvShowsCommand(),
          'get'     => array($this, 'getTvShowScraper'),
          'update'  => array($this->details, 'updateTvShowFromScraper'),
        ),
        'tvEpisode' => array(
          'command' => $this->getScanTvEpisodesCommand(),
          'get'     => array($this, 'getTvEpisodeScraper'),
          'update'  => array($this->details, 'updateTvEpisodeFromScraper'),
        ),
    );

    foreach($toScan as $class => $config)
    {
      $this->scan($class, $config['command'], $config['get']);
      $transaction = Yii::app()->getDb()->beginTransaction();
      try {
        $this->updateDatabase($class, $config['update']);
        $transaction->commit();
      } catch (Exception $e) {
        $transaction->rollback();
        throw $e;
      }
    }
  }

  protected function getScanTvEpisodesCommand()
  {
    return Yii::app()->getDb()->createCommand(<<<EOD
SELECT e.id id, e.season season, e.episode episode, s.tvdbId tvdbId, s.title as tvShow_title
  FROM tvShow s,
       ( SELECT * FROM tvEpisode e
          WHERE e.description IS NULL
          AND e.lastTvdbUpdate < :rescanTime
       ) e
 WHERE s.tvdbId NOT NULL
 AND e.episode < 10000
 AND s.id = e.tvShow_id
EOD
    )->bindValue(':rescanTime', time()-$this->tvShowRescanTime);
  }

  public function getScanTvShowsCommand()
  {
    return Yii::app()->getDb()->createCommand(<<<EOD
SELECT id,title
  FROM tvShow
 WHERE description IS NULL
   AND lastTvdbUpdate < :rescanTime
EOD
    )->bindValue(':rescanTime', time()-$this->tvShowRescanTime);
  }

  protected function getTvEpisodeScraper($resultRow)
  {
    echo "Looking for {$resultRow['tvShow_title']}: Season {$resultRow['season']}, Episode {$resultRow['episode']}\n";
    return $this->details->getTvEpisodeScraper($resultRow['tvdbId'], $resultRow['season'], $resultRow['episode']);
  }

  protected function getTvShowScraper($resultRow)
  {
    echo "Looking for {$resultRow['title']}\n";
    return $this->details->getTvShowScraperByTitle($resultRow['title']);
  }

  protected function scan($class, $command, $getScraperCallback)
  {
    $scanned = array();
    foreach($command->queryAll() as $row) {
      $this->scanned[] = $row['id'];
      $scraper = call_user_func($getScraperCallback, $row);
      if($scraper)
      {
        echo "Found! Will update ".$scraper->name."\n";
        $this->toSave[$row['id']] = $scraper;
      }
    }
  }

  protected function updateDatabase($class, $updateFromScraperCallback)
  {
    $model = CActiveRecord::model($class);
    if(count($this->scanned))
    {
      echo "Marking ".count($this->scanned)." {$class}s as scanned ...";
      $model->updateByPk($this->scanned, array('lastTvdbUpdate'=>time()));
      $this->scanned = array();
      echo "Done.\n";
    }
    if(count($this->toSave))
    {
      $saved = 0;
      foreach($this->toSave as $id => $scraper)
      {
        echo "Saving {$scraper->name} ...";
        $record = $model->findByPk($id);
        if($record && call_user_func($updateFromScraperCallback, $record, $scraper))
          ++$saved;
        else
          echo "Failed";
        echo "\n";
      }
      $this->toSave = array();
      echo "Saved $saved {$class}s\n";
    }
  }
}

