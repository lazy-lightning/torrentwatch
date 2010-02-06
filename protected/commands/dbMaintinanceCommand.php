<?php

class dbMaintinanceCommand extends BaseConsoleCommand {
  public function run($args) {
    $db = Yii::app()->db;

    // NOTE: these querys are run after pruning the feed items table to a max count specified in dvrConfig
    $querys = array(
        // create temp table containing feeditems that point to others that are also in movies
        // perhaps this should be run after feed update instead?
        'CREATE TEMP TABLE convert AS'.
        ' SELECT i.id as feedItem_id, m.id as movie_id, o.id as other_id'.
        ' FROM movie m,other o,feedItem i'.
        ' WHERE i.other_id = o.id'.
        ' AND o.title LIKE m.title;'
        ,
        // point all feeditems found to those movies
        'UPDATE feedItem'.
        '   SET movie_id=(SELECT movie_id FROM convert c WHERE c.feedItem_id = feedItem.id),'.
        '       other_id = null'.
        ' WHERE feedItem.id IN(select feedItem_id FROM convert);'
        ,
        // drop temp table - may be unneccessary
        'DROP TABLE convert;'
        ,
        // Delete others that dont point to a feed item anymore
        // Unless they have been marked downloaded, so it doesn't download same title in future
        'DELETE FROM other'.
        ' WHERE id NOT IN (SELECT other_id FROM feedItem WHERE other_id NOT NULL)'.
        '   AND status = '.other::STATUS_NEW.';'
        ,
        // Delete movies that dont point to a feed item anymore
        // Unless they have been marked downloaded, so it doesn't download same title in future
        'DELETE FROM movie'.
        ' WHERE id NOT IN (SELECT movie_id FROM feedItem WHERE movie_id NOT NULL)'.
        '   AND status = '.movie::STATUS_NEW.';'
        ,
        // Delete tv episodes that dont point to a feed item anymore
        // Unless they have been marked downloaded, so it doesn't download same title in future
        'DELETE FROM tvEpisode'.
        ' WHERE id NOT IN (SELECT tvEpisode_id FROM feedItem WHERE tvEpisode_id NOT NULL)'.
        '   AND status = '.tvEpisode::STATUS_NEW.';'
        ,
        // Delete tvShows that dont point to a tvEpisode or a favorite
        'DELETE FROM tvShow'.
        ' WHERE id NOT IN (SELECT tvShow_id FROM tvEpisode)'.
        '   AND id NOT IN (SELECT tvShow_id FROM favoriteTvShows);'
        ,
        // Empty out unrelated networks
        'DELETE FROM network'.
        ' WHERE id NOT IN (SELECT network_id FROM tvShow);'
        ,
        // Empty out unrelated genres
        'DELETE FROM genre'.
        ' WHERE id NOT IN (SELECT genre_id from favoriteMovies)'.
        '   AND id NOT IN (SELECT genre_id from movie_genre)'.
        '   AND id NOT IN (SELECT genre_id from tvShow_genre);'
        ,
    );
    // SQLite doesn't do anything with foreign keys, so clean out MANY_MANY relationships that point to
    // non-existant things
    $pruneFk = array(
         array( 'table' => 'feedItem_quality',        'fktable' => 'feedItem'),
         array( 'table' => 'favoriteMovies_quality',  'fktable' => 'favoriteMovies'),
         array( 'table' => 'favoriteStrings_quality', 'fktable' => 'favoriteStrings'),
         array( 'table' => 'favoriteTvShows_quality', 'fktable' => 'favoriteTvShows'),
         array( 'table' => 'tvShow_genre',            'fktable' => 'tvShow'),
         array( 'table' => 'movie_genre',             'fktable' => 'movie'),
    );
    // could bind params instead with a custom createCommand(), but this is simple and straightforward, no chance of injection attack
    // and its not run interactively so the little bit of extra speed is negligable
    foreach($pruneFk as $item)
      $querys[] = "DELETE FROM {$item['table']} WHERE {$item['fktable']}_id NOT IN (SELECT id FROM {$item['fktable']});";
    $transaction = $db->beginTransaction();
    try {
      // delete more than maxItemsPerFeed
      $reader = $db->createCommand('SELECT id FROM feed')->queryAll();
      foreach($reader as $row)
      {
        $querys[] =
          'DELETE FROM feedItem'.
          ' WHERE id IN ( SELECT id FROM feedItem'.
                        '  WHERE feed_id = '.$row['id'].
                        '  ORDER BY pubDate DESC'.
                        '  LIMIT -1'.
                        ' OFFSET '.(int)(Yii::app()->dvrConfig->maxItemsPerFeed).
                        ');';
      }

      // Run the pre-defined querys from above
      foreach($querys as $sql)
      {
        $db->createCommand($sql)->execute();
      }

      $transaction->commit();
    }
    catch (Exception $e)
    {
      $transaction->rollback();
      throw $e;
    }
  }
}
