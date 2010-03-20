<?php

class scrapeRunner {

  /**
   * modelClass 
   * 
   * @var string
   */
  protected $modelClass;

  /**
   * flush scrapes to the database every $flush items.  Default of 0 means
   * to wait untill all scrapes are complete.
   *
   * @var integer
   */
  public $flush = 0;

  /**
   * limit the number of scrapes to perform before exiting.  Default of 0
   * means to scrape all result rows
   * 
   * @var float
   */
  public $limit = 0;

  /**
    * @var ScraperAdapter
    */
  protected $details;

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
   * rescanTime 
   * 
   * @var integer the time in secconds to wait before rescanning for data when no info is found
   *              default of 172800 makes for 48 hours.
   */
  public $rescanTime = 172800;


  public function __construct($adapter)
  {
    if(!$adapter instanceof ScraperAdapter)
      throw new CException(get_class($adapter)." is not a ScraperAdapter");
    $this->details = $adapter;
    $this->details->init();
  }

  public function run($toScan = null)
  {
    if($toScan === null)
      $toScan = $this->createScanCommand()->queryAll();
    if(!is_array($toScan))
      throw new CException('Invalid scan data.  Expected array got '.gettype($scanCommand));
    elseif(count($toScan) === 0)
      echo "No results to scrape.\n";
    else
    {
      $this->modelClass = $this->details->modelClass;
      $this->scan($toScan);
    }
  }

  protected function createScanCommand($rescanTime = null, $attributes = array())
  {
    if($rescanTime === null)
      $rescanTime = $this->rescanTime;
    return $this->details->createScanCommand($rescanTime, $attributes);
  }

  protected function getScraper($search)
  {
    if(isset($search['title']))
      echo "Looking for {$search['title']}\n";
    elseif(isset($search['name']))
      echo "Looking for {$search['name']}\n";
    else
      echo "Searching . . .\n";
    return $this->details->getScraper($search);
  }

  protected function scan($toScan)
  {
    $scanned = array();
    $flushCount = 0;
    $totalCount = 0;
    foreach($toScan as $row) 
    {
      if(($scraper = $this->getScraper($row)))
      {
        echo "Found! Will update ".$scraper->getName()."\n";
        $this->toSave[$row['model_id']] = $scraper;
        if($this->flush && ++$flushCount >= $this->flush)
        {
          $this->updateDatabase();
          $flushCount = 0;
        }
        if(!$this->limit == 0 && ++$totalCount >= $this->limit)
        {
          echo "Hit scrape count limit.\n";
          break;
        }
      }
      else
      {
        echo "Better luck next time.\n";
        $this->scanned[] = $row['model_id'];
      }
    }
    $this->updateDatabase();
  }

  protected function processScanned()
  {
    echo "Marking ".count($this->scanned)." {$this->modelClass}s as scanned ...";
    // maybee slow, but easy instead of using an SQL IN clause
    $now = time();
    foreach($this->scanned as $model_id)
      $this->details->recordScrape($model_id, null, $now);
    $this->scanned = array();
    echo "Done.\n";
  }

  protected function processToSave()
  {
    $model = CActiveRecord::model($this->modelClass);
    $now = time();
    $saved = 0;
    foreach($this->toSave as $model_id => $scraper)
    {
      echo "Saving {$scraper->getName()} ...";
      $this->details->recordScrape($model_id, $scraper->getId(), $now);
      if($this->details->updateFromScraper($model_id, $scraper))
        ++$saved;
      else
        echo "Failed";
      echo "\n";
    }
    $this->toSave = array();
    echo "Saved $saved {$this->modelClass}s\n";
  }

  protected function updateDatabase()
  {
    $transaction = Yii::app()->getDb()->beginTransaction();
    try {
      if(count($this->scanned))
        $this->processScanned();
      if(count($this->toSave))
        $this->processToSave();
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
  }
}

