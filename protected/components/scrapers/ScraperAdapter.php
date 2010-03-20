<?php

abstract class ScraperAdapter {

  public $table = '';

  public $modelClass = '';

  private $modelColumn;
  private $scraperColumn;

  /**
   * builder 
   * 
   * @var CDbCommandBuilder
   */
  protected $builder;

  /**
   * factory 
   * 
   * @var modelFactory
   */
  protected $factory;

  abstract public function getScraper($resultRow);
  abstract public function updateFromScraper($model, $scraper);

  public function __construct($db=null, $factory=null)
  {
    if(empty($this->table) || empty($this->modelClass))
      throw new CException('Basic configuration of '.get_class($this).' not completed');
    if($db === null)
      $db = Yii::app()->getDb();
    $this->db = $db;
    if($factory === null)
      $factory = Yii::app()->modelFactory;
    $this->factory = $factory;
    $this->builder = $db->getSchema()->getCommandBuilder();
  }

  public function getModelColumn()
  {
    return $this->modelColumn;
  }

  public function getScraperColumn()
  {
    return $this->scraperColumn;
  }

  public function init()
  {
    $schema = $this->db->getSchema()->getTable($this->table);
    if($schema === null)
      throw new CException('Database does not have a table named '.$this->table);
    if(count($schema->columns) !== 3 || !isset($schema->columns['lastUpdated']))
      throw new CException('Database does not have expected columns');
    foreach($schema->columns as $column => $data)
    {
      if($column === 'lastUpdated')
        continue;
      if(false !== strpos($column, '_'))
      {
        if($this->modelColumn !== null)
          throw new CException('Database does not have expected columns');
        $this->modelColumn = $column;
      }
      else
      {
        if($this->scraperColumn !== null)
          throw new CException('Database does not have expected columns');
        $this->scraperColumn = $column;
      }
    }

  }

  protected function createRecordExistsCommand($model_id)
  {
    return $this->builder->createCountCommand($this->table,
        new CDbCriteria(array(
            'condition' => "{$this->modelColumn} = :model_id"
        ))
    )->bindValue(':model_id', $model_id);
  }

  protected function createInsertScrapeCommand($model_id, $scraperId)
  {
    return $this->builder->createInsertCommand($this->table,
        array(
            $this->modelColumn => $model_id,
            $this->scraperColumn => $scraperId,
            'lastUpdated' => time(),
        )
    );
  }

  public function createScanCommand($timeLimit, $columns)
  {
    $modelTable = CActiveRecord::model($this->modelClass)->tableName();
    $columnsSql = '';
    if(count($columns))
    {
      foreach($columns as $n => $col)
      {
        if(false === strpos($col, '.'))
          $columns[$n] = "model.$col AS $col";
      }
      $columnsSql = ', '.implode(', ', $columns);
    }
    $timeLimitSql = '';
    if($timeLimit)
    {
      $timeLimit = time()-$timeLimit;
      $timeLimitSql = <<<EOD
WHERE ( scraper.lastUpdated IS NULL
        OR ( scraper_id IS NULL
             AND
             scraper.lastUpdated < {$timeLimit}
           )
      )
EOD;
    }

    return $this->createScanCommandReal($columnsSql, $modelTable, $timeLimitSql);
  }

  protected function createScanCommandReal($columnsSql, $modelTable, $timeLimitSql)
  {
    return $this->db->createCommand(<<<EOD
SELECT model.id as model_id, scraper.{$this->scraperColumn} as scraper_id $columnsSql
  FROM {$modelTable} model
  LEFT OUTER JOIN {$this->table} scraper
    ON  model.id = scraper.{$this->modelColumn}
  $timeLimitSql
EOD
    );
  }

  protected function createUpdateScrapeCommand($model_id, $scraperId)
  {
    return $this->builder->createUpdateCommand($this->table,
        array(
          'lastUpdated' => time(), 
          $this->scraperColumn => $scraperId
        ),
        new CDbCriteria(array(
            'condition' => "{$this->modelColumn} = :modelColumn"
        ))
    )->bindValue(':modelColumn', $model_id);
  }

  protected function logError($model)
  {
    Yii::log("Error saving ".get_class($model)." after update", CLogger::LEVEL_ERROR, 'application.components.scrapers.ScrapeAdapter');
    Yii::log(print_r($model->errors, true), CLogger::LEVEL_ERROR, 'application.components.scrapers.ScrapeAdapter');
  }

  public function recordScrape($model_id, $scraperId)
  {
    if($this->createRecordExistsCommand($model_id)->queryScalar())
      $cmd = $this->createUpdateScrapeCommand($model_id, $scraperId);
    else
      $cmd = $this->createInsertScrapeCommand($model_id, $scraperId);
    $cmd->execute();
  }

}
