<?php

class migrateFromVersionThree extends dbMigration {
  public function run()
  {
    $this->replaceView('matchingFavoriteTvShows', $this->getTvShowsSql());
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
   AND ( f.minSeason = 0
         OR i.tvEpisode_season >= f.minSeason )
   AND ( f.maxSeason = 0
         OR i.tvEpisode_season <= f.maxSeason )
   AND ( f.minEpisode = 0
         OR i.tvEpisode_episode >= f.minEpisode )
   AND ( f.maxEpisode = 0
         OR i.tvEpisode_episode <= f.maxEpisode );
EOD
  ;
  }
}

