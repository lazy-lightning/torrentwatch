<?php

class migrateFromVersionOne extends dbMigration {
  public function run()
  {
    $this->replaceView('newestTvEpisode',
        'SELECT *'.
        '  FROM ( SELECT * FROM tvEpisode'. 
        '         ORDER BY season,episode'.
        '       )'.
        ' GROUP BY tvShow_id'
    );
    $this->addColumn('tvShow', 'hide');
    $this->setDbVersion(2);
  }
}

