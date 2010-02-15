<?php

class migrateFromVersionZero extends dbMigration {
  public function run()
  {
    $this->addColumn('movie', 'lastUpdated INTEGER');
    $this->addColumn('other', 'lastUpdated INTEGER');
    $this->db->createCommand(
        'UPDATE dvrConfig SET value="Default" WHERE key="category" AND dvrConfigCategory_id = 7'
    )->execute();
    $this->setDbVersion(1);
  }
}

