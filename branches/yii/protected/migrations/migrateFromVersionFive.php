<?php

class migrateFromVersionFive extends dbMigration
{
  public function run()
  {
    $this->createTable('TVDbTvShowAdapter', <<<EOD
    tvShow_id INTEGER NOT NULL PRIMARY KEY,
    tvdbShowId INTEGER,
    lastUpdated INTEGER NOT NULL
EOD
    );
    $this->createTable('TVDbTvEpisodeAdapter', <<<EOD
    tvEpisode_id INTEGER NOT NULL PRIMARY KEY,
    tvdbEpisodeId INTEGER,
    lastUpdated INTEGER NOT NULL
EOD
    );
    $this->createTable('IMDbMovieAdapter', <<<EOD
    movie_id INTEGER NOT NULL PRIMARY KEY,
    imdbId INTEGER,
    lastUpdated INTEGER NOT NULL
EOD
    );
    $this->createTable('IMDbOtherAdapter', <<<EOD
    other_id INTEGER NOT NULL PRIMARY KEY,
    unused INTEGER,
    lastUpdated INTEGER NOT NULL
EOD
    );
    $this->createTable('TVcomTvShowAdapter', <<<EOD
    tvShow_id INTEGER NOT NULL PRIMARY KEY,
    tvComId INTEGER,
    lastUpdated INTEGER NOT NULL
EOD
    );

    $this->recreateTable('tvShow',
        <<<EOD
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    network_id INTEGER,
    title TEXT,
    description TEXT, 
    rating INTEGER, 
    hide INTEGER DEFAULT 0,
    FOREIGN KEY (network_id) REFERENCES network(id)
EOD
        ,array('id', 'network_id', 'title', 'description', 'rating', 'hide')
    );
    $this->recreateTable('tvEpisode',
        <<<EOD
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    tvShow_id INTEGER,
    season INTEGER NOT NULL, 
    episode INTEGER NOT NULL,
    title TEXT,
    description TEXT,
    lastUpdated INTEGER,
    status INTEGER, 
    firstAired INTEGER, 
    FOREIGN KEY (tvShow_id) REFERENCES tvShow(id)
EOD
        ,array('id', 'tvShow_id', 'season', 'episode', 'title', 'description', 'lastUpdated', 'status', 'firstAired')
    );
    $this->recreateTable('movie',
        <<<EOD
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    title TEXT,
    status INTEGER NOT NULL DEFAULT 0,
    name TEXT,
    year TEXT,
    runtime TEXT,
    rating INTEGER,
    plot TEXT, 
    lastUpdated INTEGER
EOD
        ,array('id', 'title', 'status', 'name', 'year', 'runtime', 'rating', 'plot', 'lastUpdated')
    );
    $this->recreateTable('other',
        <<<EOD
      id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
      title TEXT,
      status INTEGER NOT NULL DEFAULT 0,
      lastUpdated INTEGER
EOD
        ,array('id', 'title', 'status', 'lastUpdated')
    );
    $this->setDbVersion(6);
  }
}
