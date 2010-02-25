<?php

class migrateFromVersionThree extends dbMigration {
  public function run()
  {
    $this->replaceView('matchingFavoriteTvShows', $this->getTvShowsSql());
    $this->replaceView('matchingFavoriteMovies', $this->getMoviesSql());
    $this->replaceView('matchingFavoriteStrings', $this->getStringsSql());
    $this->setDbVersion(4);
  }

  protected function getTvShowsSql()
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
  FROM tvFeedItem i, favoriteTvShows f
  LEFT OUTER JOIN
       (SELECT favoriteTvShows_id, count(quality_id) as count FROM favoriteTvShows_quality GROUP BY favoriteTvShows_id) q
    ON q.favoriteTvShows_id = f.id
 WHERE i.tvShow_id = f.tvShow_id
   AND ( f.feed_id = 0
         OR
         f.feed_id = i.feed_id 
       )
   AND NOT i.tvEpisode_episode = 0
   AND ( f.minSeason = 0
         OR i.tvEpisode_season >= f.minSeason )
   AND ( f.maxSeason = 0
         OR i.tvEpisode_season <= f.maxSeason )
   AND ( f.minEpisode = 0
         OR i.tvEpisode_episode >= f.minEpisode )
   AND ( f.maxEpisode = 0
         OR i.tvEpisode_episode <= f.maxEpisode )
   AND ( q.count IS NULL
         OR
         q.count = ( SELECT COUNT(*)
                       FROM feedItem_quality iq, favoriteTvShows_quality fq
                      WHERE i.feedItem_id = iq.feedItem_id
                        AND iq.quality_id = fq.quality_id
                        AND fq.favoriteTvShows_id = f.id
                   )
       );
EOD
  ;
  }
  protected function getStringsSql()
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
  FROM favoriteStrings f, feedItem i, feed
  LEFT OUTER JOIN
       (SELECT favoriteStrings_id, count(quality_id) as count FROM favoriteStrings_quality GROUP BY favoriteStrings_id) q
    ON q.favoriteStrings_id = f.id
 WHERE feed.id = i.feed_id
   AND ( f.feed_id = 0
         OR
         f.feed_id = i.feed_id
       )
   AND ( q.count IS NULL
         OR
         q.count = ( SELECT COUNT(*)
                       FROM feedItem_quality iq, favoriteStrings_quality fq
                      WHERE i.id = iq.feedItem_id
                        AND iq.quality_id = fq.quality_id
                        AND fq.favoriteStrings_id = f.id
                   )
       )
   AND i.title LIKE f.filter
   AND ( f.notFilter IS NULL
         OR
         i.title NOT LIKE f.notFilter
       );
EOD
  ;
  }
  protected function getMoviesSql()
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
  LEFT OUTER JOIN
       (SELECT favoriteMovies_id, count(quality_id) as count FROM favoriteMovies_quality GROUP BY favoriteMovies_id) q
    ON q.favoriteMovies_id = f.id
 WHERE i.movie_id = m.id
   AND mg.movie_id = m.id
   AND ( f.genre_id = 0
         OR
         f.genre_id = mg.genre_id
       )
   AND m.rating >= f.rating
   AND m.year BETWEEN f.minYear AND f.maxYear
   AND ( f.feed_id = 0
         OR
         f.feed_id = i.feed_id
       )
   AND ( q.count IS NULL
         OR
         q.count = ( SELECT COUNT(*)
                       FROM feedItem_quality iq, favoriteMovies_quality fq
                      WHERE i.id = iq.feedItem_id
                        AND iq.quality_id = fq.quality_id
                        AND fq.favoriteMovies_id = f.id
                   )
       );
EOD
  ;
  }
}

