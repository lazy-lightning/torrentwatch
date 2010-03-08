<?php

/**
 * migrateFromVersionOne encompases database changes made in r777
 * 
 * @uses dbMigration
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class migrateFromVersionFour extends dbMigration {
  public function run()
  {
    // r777
    $this->db->createCommand(
          "INSERT INTO dvrConfig (key, value, dvrConfigCategory_id) VALUES('submitUsageLogs', 1, NULL)"
    )->execute();
    $this->db->createCommand(
          "INSERT INTO dvrConfig (key, value, dvrConfigCategory_id) VALUES('checkNewVersion', 1, NULL)"
    )->execute();

    // r777
    $this->addIndex('feedItem_tvEpisode', 'feedItem(tvEpisode_id,status)');
    $this->dropIndex('feedItem_status');
    $this->dropIndex('feedItem_pubDate');

    $this->setDbVersion(5);
  }
}

