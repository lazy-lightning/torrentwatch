<?php

require_once('TVDB.php');

class TVDbTvEpisodeAdapter extends ScraperAdapter
{
  public $table = 'TVDbTvEpisodeAdapter';
  public $modelClass = 'tvEpisode';
  
  /**
   * tvShowAdapter 
   * may also be a valid class name
   * @var ScraperAdapter
   */
  public $tvShowAdapter = 'TVDbTvShowAdapter';

  public function createScanCommand($timeLimit, $columns = array())
  {
    return parent::createScanCommand($timeLimit, array_merge($columns, array(
        'season',
        'episode',
        "tvShow.title||': Season '||model.season||', Episode '||model.episode AS title",
        'tvShowScraper.'.$this->tvShowAdapter->getScraperColumn().' AS tvdbTvShow_id',
    )));
  }

  /**
   * createScanCommandReal 
   * modified from ScraperAdapter implementation by adding second outer join 
   *
   * @param string $columnsSql 
   * @param string $modelTable 
   * @param string $timeLimitSql 
   * @return CDbCommand
   */
  protected function createScanCommandReal($columnsSql, $modelTable, $timeLimitSql)
  {
    $tvShowClass = CActiveRecord::model($this->tvShowAdapter->modelClass)->tableName();
    $tvShowSql = (empty($timeLimitSql)?'WHERE':'AND').' model.tvShow_id = tvShow.id';
    $cmd = $this->db->createCommand(<<<EOD
SELECT DISTINCT model.id as model_id, scraper.{$this->getScraperColumn()} as scraper_id $columnsSql
  FROM {$modelTable} model, {$tvShowClass} tvShow
  LEFT OUTER JOIN {$this->table} scraper
    ON  model.id = scraper.{$this->getModelColumn()}
  LEFT OUTER JOIN {$this->tvShowAdapter->table} tvShowScraper
    ON model.tvShow_id = tvShowScraper.tvShow_id
  $timeLimitSql
  $tvShowSql
EOD
    );
    return $cmd;
  }

  public function getScraper($resultRow)
  {
    if($resultRow['episode'] > 1000)
      return false; // dont have methods for date based episodes
    return TV_Episodes::search($resultRow['tvdbTvShow_id'], $resultRow['season'], $resultRow['episode']);
  }

  public function init()
  {
    parent::init();
    if(is_array($this->tvShowAdapter) || is_string($this->tvShowAdapter))
      $this->tvShowAdapter = Yii::createComponent($this->tvShowAdapter, $this->db, $this->factory);
    if(!$this->tvShowAdapter instanceof ScraperAdapter)
      throw new CException('Expected tvShowAdapter to be a ScraperAdapter instead got '.get_class($this->tvShowAdapter));
    $this->tvShowAdapter->init();
  }

  public function updateFromScraper($tvEpisode_id, $scraper)
  {
    $tvEpisode = tvEpisode::model()->findByPk($tvEpisode_id);
    if(!$tvEpisode instanceof tvEpisode)
      return false;
    if(!empty($scraper->firstAired))
      $tvEpisode->firstAired = $scraper->firstAired;
    if(!empty($scraper->description) && strlen($scraper->overview) > strlen($tvEpisode->description))
      $tvEpisode->description = $scraper->overview;
    if(!empty($scraper->name))
      $tvEpisode->title = $scraper->name;
    if($tvEpisode->save() === false)
    {
      $this->logError($tvEpisode);
      return false;
    }
    return true;
  }

}
