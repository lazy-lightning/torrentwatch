CREATE VIEW matchingFavoriteTvShows AS
SELECT favoriteTvShows.id as favoriteTvShows_id,
       favoriteTvShows.saveIn as favorite_saveIn,
       favoriteTvShows.onlyNewer as favorite_onlyNewer,
       feedItem.id as feedItem_id,
       feedItem.title as feedItem_title,
       feed.id as feed_id,
       tvShow.id as tvShow_id,
       tvEpisode.id as tvEpisode_id,
       tvEpisode.season as season,
       tvEpisode.episode as episode,
       favoriteTvShows.quality_id as quality_id,
       tvEpisode.status as tvEpisode_status,
       feedItem.status as feedItem_status,
       feedItem.url as feedItem_url,
       feed.url as feed_url
  FROM favoriteTvShows, tvShow, tvEpisode, feed, feedItem, feedItem_quality
  WHERE tvShow.id = favoriteTvShows.tvShow_id
    AND tvEpisode.tvShow_id=tvShow.id
    AND feedItem.tvEpisode_id=tvEpisode.id
    AND feedItem_quality.feedItem_id=feedItem.id
    AND feed.id=feedItem.feed_id
    AND ( favoriteTvShows.feed_id=feedItem.feed_id OR favoriteTvShows.feed_id=0 )
    AND ( favoriteTvShows.quality_id=feedItem_quality.quality_id OR favoriteTvShows.quality_id=0 );

CREATE VIEW matchingFavoriteStrings AS
SELECT favoriteStrings.id as favoriteStrings_id,
       favoriteStrings.saveIn as favorite_saveIn,
       feedItem.id as feedItem_id,
       feedItem.title as feedItem_title,
       feedItem.feed_id as feed_id,
       feed.url as feed_url,
       feedItem.url as feedItem_url,
       feedItem.status as feedItem_status
  FROM favoriteStrings, feedItem, feed
 WHERE favoriteStrings.filter LIKE feedItem.title
   AND favoriteStrings.notFilter NOT LIKE feedItem.title
   AND feedItem.feed_id = feed.id 
   AND ( favoriteStrings.feed_id = feedItem.feed_id OR favoriteStrings.feed_id = 0 );

CREATE VIEW matchingFavoriteMovies AS
SELECT favoriteMovies.id as favoriteMovies_id,
       favoriteMovies.saveIn as favorite_saveIn,
       feedItem.id as feedItem_id,
       feedItem.title as feedItem_title,
       feedItem.feed_id as feed_id,
       feed.url as feed_url,
       feedItem.url as feedItem_url,
       feedItem.status as feedItem_status
  FROM favoriteMovies, feedItem, movies, genre, movies_genre
 WHERE feedItem.movie_id = movie.id
   AND movies_genre.movie_id = movie.id
   AND movies_genre.genre_id = genre.id
   AND ( favoriteMovies.genre_id = genre.id OR favoriteMovies.genre_id = 0 )
   AND favoriteMovies.rating <= movie.rating;

CREATE TABLE movie (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    plot TEXT,
    imdbId INTEGER,
    rating NUMERIC
);

CREATE TABLE movie_genre (
    genre_id INTEGER NOT NULL,
    movie_id INTEGER NOT NULL,
    FOREIGN KEY (genre_id) REFERENCES genre(id),
    FOREIGN KEY (movie_id) REFERENCES movie(id)
);

CREATE TABLE favoriteMovies (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    rating NUMERIC NOT_NULL DEFAULT 10,
    feed_id INTEGER NOT NULL DEFAULT 0,
    genre_id INTEGER NOT NULL DEFAULT 0,
    quality_id INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (feed_id) REFERENCES feed(id),
    FOREIGN KEY (genre_id) REFERENCES genre(id),
    FOREIGN KEY (quality_id) REFERENCES quality(id)
);

CREATE TABLE favoriteStrings (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    filter TEXT NOT NULL,
    notFilter TEXT NOT NULL DEFAULT "",
    feed_id INTEGER NOT NULL DEFAULT 0,
    quality_id INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (feed_id) REFERENCES feed(id),
    FOREIGN KEY (quality_id) REFERENCES quality(id)
);

