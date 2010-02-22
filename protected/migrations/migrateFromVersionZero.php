<?php

class migrateFromVersionZero extends dbMigration {
  public function run()
  {
    $this->addColumn('movie', 'lastUpdated INTEGER');
    $this->addColumn('other', 'lastUpdated INTEGER');

    // Update a few items to new default configurations
    $this->db->createCommand(
        'UPDATE dvrConfig SET value="Default" WHERE key="category" AND dvrConfigCategory_id = 7'
    )->execute();
    $this->db->createCommand(
        'UPDATE dvrConfig SET value=2 WHERE key="feedItemLifetime" AND dvrConfigCategory_id IS NULL'
    )->execute();
    $this->db->createCommand(
        'UPDATE dvrConfig SET value=100 WHERE key="maxItemsPerFeed" AND dvrConfigCategory_id IS NULL'
    )->execute();


    // Add two new dvrConfig attributes
    // drop them first if they exist
    $this->db->createCommand(
        'DELETE FROM dvrConfig WHERE key="gayauiTheme" AND dvrConfigCategory_id IS NULL'
    )->execute();
    $this->db->createCommand(
        'DELETE FROM dvrConfig WHERE key="webuiTheme" AND dvrConfigCategory_id IS NULL'
    )->execute();

    $this->db->createCommand(
        'INSERT INTO dvrConfig (key, value, dvrConfigCategory_id) VALUES ("webuiTheme", "classic", NULL)'
    )->execute();
    $this->db->createCommand(
        'INSERT INTO dvrConfig (key, value, dvrConfigCategory_id) VALUES ("gayauiTheme", "gaya", NULL)'
    )->execute();
    $this->setDbVersion(1);
  }
}

