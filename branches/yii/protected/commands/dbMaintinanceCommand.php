<?php

class dbMaintinanceCommand extends CConsoleCommand {
  public function run($args) {
    $db = Yii::app()->db;

    $querys = array(
        //  Delete items more than the configured  days old
        'DELETE FROM feedItem'.
        ' WHERE feedItem.pubDate < '.(time()-3600*24*Yii::app()->dvrConfig->feedItemLifetime).';',
        // Delete others that dont point to a feed item anymore
        'DELETE FROM other'.
        ' WHERE id NOT IN (SELECT other_id FROM feedItem);',
        // Delete movies that dont point to a feed item anymore
        'DELETE FROM movie'.
        ' WHERE id NOT IN (SELECT movie_id FROM feedItem);',
        // Delete tvShows that dont point to a feed item(indirectly) or a favorite
        'DELETE FROM tvShow'.
        ' WHERE id NOT IN (SELECT tvShow_id FROM tvEpisode WHERE id IN (SELECT tvEpisode_id FROM feedItem))'.
        '   AND id NOT IN (SELECT tvShow_id FROM favoriteTvShows);',
        // Delete tvEpisodes that point to a nonexistant tvShow or nonexistant feedItems
        // note: due to the above sql, the tvShow should only be nonexistant when no feedItems as well
        //       but we can have a tvShow and no feedItems so maybee only select on not in feedItem ?
        'DELETE FROM tvEpisode'.
        ' WHERE tvShow_id NOT IN (SELECT id FROM tvShow)'.
        '    OR id NOT IN (SELECT tvEpisode_id FROM feedItem);',
        //
    );
    // SQLite doesn't do anything with foreign keys, so clean out MANY_MANY relationships that point to
    // non-existant things
    $pruneFk = array(
         array( 'table'   => 'feedItem_quality',
                'fk'      => 'feedItem_id',
                'pktable' => 'feedItem',
         ),
         array( 'table'   => 'tvShow_genre',
                'fk'      => 'tvShow_id',
                'pktable' => 'tvShow',
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
         array( 'table'   => 'movie_genre',
                'fk'      => 'movie_id',
                'pktable' => 'movie',
         ),

    );
    foreach($pruneFk as $item)
      $querys[] = "DELETE FROM {$item['table']} WHERE {$item['fk']} NOT IN (SELECT id FROM {$item['pktable']});";

    foreach($querys as $sql)
      $db->createCommand($sql)->execute();
  }
}
