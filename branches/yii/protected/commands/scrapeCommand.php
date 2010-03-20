<?php

Yii::import('application.components.scrapers.*');

class scrapeCommand extends BaseAdapterCommand
{
  public $flags = array(
      'db'      => array(
        'type' => 'component', 
        'help' => 'A valid component of Yii::app() to use as the database,  Default: db',
      ),
      'factory' => array(
        'type' => 'component',
        'help' => 'A valid component of Yii::app() to use as the factory.  Default: modelFactory',
      ),
      'flush'   => array(
        'type' => 'integer',
        'help' => 'The number of scrapes to perform between database flushes.  Default of 0 means flush only on exit.',
      ),
      'limit'   => array(
        'type' => 'integer',
        'help' => 'The maximum number of scrapes to perform per adapter.  Default of 0 means scrape all.',
      ),
  );

  public $adapters = array(
      'tvdotcom' => array(
        'tvshow' => array(
          'class' => 'TVcomTvShowAdapter',
          'modelClass' => 'tvShow',
        ),
      //  'tvepisode' => 'TVcomTvEpisodeAdapter',
      ),
      'imdb' => array(
        'movie' => array(
          'class' => 'IMDbMovieAdapter',
          'modelClass' => 'movie',
        ),
        'other' => array(
          'class' => 'IMDbOtherAdapter',
          'modelClass' => 'other',
        ),
      ),
      'tvdb' => array(
        'tvshow' => array(
          'class' => 'TVDbTvShowAdapter',
          'modelClass' => 'tvShow',
        ),
        'tvepisode' => array(
          'class' => 'TVDbTvEpisodeAdapter',
          'modelClass' => 'tvEpisode',
          'tvShowAdapter' => array(
            'class' => 'TVDbTvShowAdapter',
            'modelClass' => 'tvShow',
          ),
        ),
      ),
  );

  public $examples =
"  scrape all
  scrape tvdb
  scrape -flush=5 tvdotcom tvshow";

  public $limit = 0;

  protected function createRunner($config)
  {
    $adapter = Yii::createComponent($config, $this->db, $this->factory);
    if(!$adapter instanceof ScraperAdapter)
      throw new CException('Expecting ScraperAdapter, got '.get_class($adapter).' instead');
    $runner = new scrapeRunner($adapter);
    $runner->flush = $this->flush;
    $runner->limit = $this->limit;
    return $runner;
  }
}

