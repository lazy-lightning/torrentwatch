<?php

class dbMaintinanceCommand extends BaseConsoleCommand {
  public function run($args) {
    $db = Yii::app()->db;

    // NOTE: these querys are run after pruning the feed items table to a max count specified in dvrConfig
    $querys = array(
        // create temp table containing feeditems that point to others that are also in movies
        'CREATE TEMP TABLE convert AS'.
        ' SELECT i.id as feedItem_id, m.id as movie_id, o.id as other_id'.
        ' FROM movie m,other o,feedItem i'.
        ' WHERE i.other_id = o.id'.
        ' AND m.title LIKE o.title;',
        // point all feeditems found to those movies
        'UPDATE feedItem'.
        '   SET movie_id=(SELECT movie_id FROM convert c WHERE c.feedItem_id = feedItem.id),'.
        '       other_id = null'.
        ' WHERE feedItem.id IN(select feedItem_id FROM convert);',
        // drop temp table
        'DROP TABLE convert;',
        // Delete others that dont point to a feed item anymore
        // Unless they have been marked downloaded, so it doesn't download same title in future
        'DELETE FROM other'.
        ' WHERE id NOT IN (SELECT other_id FROM feedItem WHERE other_id NOT NULL)'.
        '   AND status = '.other::STATUS_NEW.';',
        // Delete movies that dont point to a feed item anymore
        // Unless they have been marked downloaded, so it doesn't download same title in future
        'DELETE FROM movie'.
        ' WHERE id NOT IN (SELECT movie_id FROM feedItem WHERE movie_id NOT NULL)'.
        '   AND status = '.movie::STATUS_NEW.';',
        // Delete tvShows that dont point to a feed item(indirectly) or a favorite
        'DELETE FROM tvShow'.
        ' WHERE id NOT IN (SELECT tvShow_id FROM tvEpisode WHERE id IN (SELECT tvEpisode_id FROM feedItem WHERE tvEpisode_id NOT NULL))'.
        '   AND id NOT IN (SELECT tvShow_id FROM favoriteTvShows);',
        // Delete tvEpisodes that point to a nonexistant tvShow or nonexistant feedItems
        // note: due to the above sql, the tvShow should only be nonexistant when no feedItems as well
        //       but we can have a tvShow and no feedItems so maybee only select on not in feedItem ?
        'DELETE FROM tvEpisode'.
        ' WHERE ( tvShow_id NOT IN (SELECT id FROM tvShow)'.
        '         OR'.
        '         id NOT IN (SELECT tvEpisode_id FROM feedItem WHERE tvEpisode_id NOT NULL)'.
        '       )'.
        '   AND status = '.tvEpisode::STATUS_NEW.';',
        // Empty out unrelated networks
        'DELETE FROM network'.
        ' WHERE id NOT IN (SELECT network_id FROM tvShow);',
        // Empty out unrelated genres
        'DELETE FROM genre'.
        ' WHERE id NOT IN (SELECT genre_id from favoriteMovies)'.
        '   AND id NOT IN (SELECT genre_id from movie_genre);',
    );
    // SQLite doesn't do anything with foreign keys, so clean out MANY_MANY relationships that point to
    // non-existant things
    $pruneFk = array(
         array( 'table'   => 'feedItem_quality',
                'fk'      => 'feedItem_id',
                'pktable' => 'feedItem',
         ),
         array( 'table'   => 'favoriteMovies_quality',
                'fk'      => 'favoriteMovies_id',
                'pktable' => 'favoriteMovies',
         ),
         array( 'table'   => 'favoriteStrings_quality',
                'fk'      => 'favoriteStrings_id',
                'pktable' => 'favoriteStrings',
         ),
         array( 'table'   => 'favoriteTvShows_quality',
                'fk'      => 'favoriteTvShows_id',
                'pktable' => 'favoriteTvShows',
         ),
         array( 'table'   => 'tvShow_genre',
                'fk'      => 'tvShow_id',
                'pktable' => 'tvShow',
         ),
         array( 'table'   => 'movie_genre',
                'fk'      => 'movie_id',
                'pktable' => 'movie',
         ),
    );
    // could bind params instead with a custom createCommand(), but this is simple and straightforward, no chance of injection attack
    // and its not run interactively so the little bit of extra speed is negligable
    foreach($pruneFk as $item)
      $querys[] = "DELETE FROM {$item['table']} WHERE {$item['fk']} NOT IN (SELECT id FROM {$item['pktable']});";

    try {
      $transaction = $db->beginTransaction();
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
