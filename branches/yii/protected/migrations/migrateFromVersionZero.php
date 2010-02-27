<?php

/**
 * migrateFromVersionZero encompases changes made to the database in r501, r506
 * 
 * @uses dbMigration
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class migrateFromVersionZero extends dbMigration {
  public function run()
  {
    // r501
    $this->addColumn('movie', 'lastUpdated INTEGER');
    // r501
    $this->addColumn('other', 'lastUpdated INTEGER');

    // Update a few items to new default configurations
    // r501
    $this->db->createCommand( 
        'UPDATE dvrConfig SET value="Default" WHERE key="category" AND dvrConfigCategory_id = 7'
    )->execute();
    // r506
    $this->db->createCommand( 
        'UPDATE dvrConfig SET value=2 WHERE key="feedItemLifetime" AND dvrConfigCategory_id IS NULL'
    )->execute();
    // r506
    $this->db->createCommand(
        'UPDATE dvrConfig SET value=100 WHERE key="maxItemsPerFeed" AND dvrConfigCategory_id IS NULL'
    )->execute();


    // Add two new dvrConfig attributes
    // drop them first if they exist
    // r506
    $this->db->createCommand(
        'DELETE FROM dvrConfig WHERE key="gayauiTheme" AND dvrConfigCategory_id IS NULL'
    )->execute();
    // r506
    $this->db->createCommand( 
        'DELETE FROM dvrConfig WHERE key="webuiTheme" AND dvrConfigCategory_id IS NULL'
    )->execute();
    // r506
    $this->db->createCommand(
        'INSERT INTO dvrConfig (key, value, dvrConfigCategory_id) VALUES ("webuiTheme", "classic", NULL)'
    )->execute();
    // r506
    $this->db->createCommand(
        'INSERT INTO dvrConfig (key, value, dvrConfigCategory_id) VALUES ("gayauiTheme", "gaya", NULL)'
    )->execute();

    $this->setDbVersion(1);

  }
}

