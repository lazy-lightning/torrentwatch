<?php

/**
 * migrateFromVersionOne encompases database changes made in r549 and r561
 * 
 * @uses dbMigration
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class migrateFromVersionOne extends dbMigration {
  public function run()
  {
    // r549
    $this->replaceView('newestTvEpisode',
        'SELECT *'.
        '  FROM ( SELECT * FROM tvEpisode'. 
        '         ORDER BY season,episode'.
        '       )'.
        ' GROUP BY tvShow_id'
    );
    // r561
    $this->addColumn('tvShow', 'hide INTEGER');

    $this->setDbVersion(2);
  }
}

