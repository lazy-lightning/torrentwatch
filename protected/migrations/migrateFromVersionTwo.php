<?php

/**
 * migrateFromVersionTwo  encompases changes made to the db in r623 and r648
 * 
 * @uses dbMigration
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class migrateFromVersionTwo extends dbMigration {
  public function run()
  {
    // r623
    $this->db->createCommand(
          "INSERT INTO dvrConfig (key, value, dvrConfigCategory_id) VALUES('matchingTimeLimit', 24, NULL)"
    )->execute();
    // r623
    $this->db->createCommand(
        "DELETE FROM dvrConfig WHERE key='feedItemLifetime' AND dvrConfigCategory_id IS NULL"
    )->execute();
    // r623
    $this->replaceView('matchingFavoriteMovies', $this->getMatchingFavoriteMoviesSql());
    // r623
    $this->replaceView('matchingFavoriteStrings', $this->getMatchingFavoriteStringsSql());
    // r623
    $this->replaceView('matchingFavoriteTvShows', $this->getMatchingFavoriteTvShowsSql());


    // The status column of other table did not have a type declaration
    // which sqlite could care less about, but yii needs
    // r648
    $this->recreateTable('other', $this->getOtherColumnDef(), array(
        'id', 'title', 'status', 'lastUpdated', 'lastImdbUpdate'
    ));
    // others probably wernt getting scanned, so update 'lastImdbUpdate' to 0 so they get scanned again
    $this->db->createCommand(
        'update other set lastImdbUpdate = 0'
    )->execute();

    $this->setDbVersion(3);
  }

  protected function getMatchingFavoriteMoviesSql()
  {
    return <<<EOD
SELECT i.id feedItem_id,
       i.title feedItem_title,
       i.status feedItem_status,
       i.downloadType as feedItem_downloadType,
       i.url feedItem_url,
       i.pubDate feedItem_pubDate,
       feed.id feed_id,
       feed.url feed_url,
       feed.title feed_title,
       m.id movie_id,
       m.status movie_status,
       f.name favorite_name,
       f.id favoriteMovies_id,
       f.saveIn favorite_saveIn,
       f.queue favorite_queue
  FROM favoriteMovies f, movie m, feedItem i, movie_genre mg
  LEFT OUTER JOIN feed
    ON feed.id = i.feed_id
 WHERE i.movie_id = m.id
   AND mg.movie_id = m.id
   AND f.genre_id = mg.genre_id
   AND m.rating >= f.rating
   AND m.year BETWEEN f.minYear AND f.maxYear
   AND ( f.feed_id = 0
         OR
         f.feed_id = i.feed_id
       )
   AND ( SELECT COUNT(*)
           FROM feedItem_quality iq, favoriteMovies_quality fq
          WHERE i.id = iq.feedItem_id
            AND iq.quality_id = fq.quality_id
            AND fq.favoriteMovies_id = f.id
       );
EOD
  ;
  }

  protected function getMatchingFavoriteStringsSql()
  {
    return <<<EOD
SELECT i.id feedItem_id,
       i.title feedItem_title,
       i.status feedItem_status,
       i.downloadType as feedItem_downloadType,
       i.url feedItem_url,
       i.pubDate feedItem_pubDate,
       feed.id feed_id,
       feed.url feed_url,
       feed.title feed_title,
       f.name favorite_name,
       f.id favoriteStrings_id,
       f.saveIn favorite_saveIn,
       f.queue favorite_queue
  FROM favoriteStrings f, feedItem i, feed,
       (SELECT favoriteStrings_id, count(quality_id) AS count FROM favoriteStrings_quality GROUP BY favoriteStrings_id) fq
 WHERE feed.id = i.feed_id
   AND ( f.feed_id = 0
         OR
         f.feed_id = i.feed_id
       )
   AND f.id = fq.favoriteStrings_id
   AND ( SELECT COUNT(fq.quality_id)
           FROM feedItem_quality iq, favoriteStrings_quality fq
          WHERE iq.feedItem_id = i.id
            AND iq.quality_id = fq.quality_id
            AND fq.favoriteStrings_id = f.id
       ) = fq.count
   AND i.title LIKE f.filter
   AND ( f.notFilter IS NULL
         OR
         i.title NOT LIKE f.notFilter
       );
EOD
  ;
  }

  protected function getMatchingFavoriteTvShowsSql()
  {
    return <<<EOD
SELECT i.feedItem_id feedItem_id,
       i.feedItem_title feedItem_title,
       i.feedItem_status feedItem_status,
       i.feedItem_downloadType as feedItem_downloadType,
       i.feedItem_url feedItem_url,
       i.feedItem_pubDate feedItem_pubDate,
       i.feed_id feed_id,
       i.feed_url feed_url,
       i.feed_title feed_title,
       i.tvEpisode_id tvEpisode_id,
       i.tvEpisode_season tvEpisode_season,
       i.tvEpisode_episode tvEpisode_episode,
       i.tvEpisode_status tvEpisode_status,
       i.tvShow_id tvShow_id,
       i.tvShow_title favorite_name,
       f.id favoriteTvShows_id,
       f.saveIn favorite_saveIn,
       f.onlyNewer favorite_onlyNewer,
       f.queue favorite_queue
  FROM tvFeedItem i, favoriteTvShows f,
       (SELECT favoriteTvShows_id, count(quality_id) as count FROM favoriteTvShows_quality GROUP BY favoriteTvShows_id) fq
 WHERE i.tvShow_id = f.tvShow_id
   AND f.id = fq.favoriteTvShows_id
   AND (f.feed_id = i.feed_id OR f.feed_id = 0)
   AND ( SELECT COUNT(*)
           FROM feedItem_quality, favoriteTvShows_quality
          WHERE feedItem_quality.feedItem_id = i.feedItem_id
            AND feedItem_quality.quality_id = favoriteTvShows_quality.quality_id
            AND favoriteTvShows_quality.favoriteTvShows_id = f.id
       ) = fq.count
   AND NOT i.tvEpisode_episode = 0
   AND ( ( f.minSeason = 0
           AND f.maxSeason = 0
           AND f.minEpisode = 0
           AND f.maxEpisode = 0
         )
         OR
         ( i.tvEpisode_season BETWEEN f.minSeason AND f.maxSeason
           AND i.tvEpisode_episode BETWEEN f.minEpisode AND f.maxEpisode
         )
       );
EOD
  ;
  }

  protected function getOtherColumnDef()
  {
    return <<<EOD
      id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
      title TEXT,
      status INTEGER NOT NULL DEFAULT 0,
      lastUpdated INTEGER,
      lastImdbUpdate INTEGER NOT NULL DEFAULT 0
EOD
    ;
  }

}
