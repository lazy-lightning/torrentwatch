<?php

// client class accessed by controllers to start downloads
abstract class favoriteManager extends CComponent {
  abstract function startDownload($opts, $status);

  // there must be a better way than the following for multiple favorites . . .
  /**
   * looks for feedItems that matching a favorite in the database
   * @param integer a feeditem status to limit the search to
   */
  public function checkFavorites($itemStatus = feedItem::STATUS_NEW) {
    $this->checkTvShowFavorites($itemStatus);
    $this->checkMovieFavorites($itemStatus);
    $this->checkStringFavorites($itemStatus);

    // if we were checking new items, change all still new to nomatch
    if($itemStatus === feedItem::STATUS_NEW) {
      feedItem::model()->updateAll(
          array('status'=>feedItem::STATUS_NOMATCH),
          'status = :status',
          array(':status'=>feedItem::STATUS_NEW)
      );
    }
    
  }
 
  /**
   * looks for feedItems that matching a favoriteMovie in the database
   * @param integer a feeditem status to limit the search to
   */
  public function checkMovieFavorites($itemStatus = feedItem::STATUS_NEW) {
    Yii::log('Looking for movie favorites', CLogger::LEVEL_ERROR);
    $db = Yii::app()->db;
    $toStart = array();
    $duplicates = array();

    // Mark any previously downloaded movies that are now matching
    $db->createCommand(
      'UPDATE feedItem'.
      '   SET status='.feedItem::STATUS_DUPLICATE.
      ' WHERE feedItem.status = '.$itemStatus.
      '   AND feedItem.id IN ( SELECT feedItem_id'.
                            '    FROM matchingFavoriteMovies m'.
                            '   WHERE m.movie_status = '.movie::STATUS_DOWNLOADED.
                            ');'
    )->execute();

    // Get any matching items from the db
    $reader = $db->createCommand(
        'SELECT * FROM matchingFavoriteMovies'.
        ' WHERE feedItem_status='.$itemStatus.
        '   AND movie_status='.movie::STATUS_NEW
    )->query();

    foreach($reader as $row) {
      $toStart[$row['movie_id']][] = $row;
    }
    
    foreach($toStart as $items) {
      // For now just take the first feedItem for each movie, but this
      // structure allows there to be a decision making process inserted
      // here to decide which item based on feed priority or something.
      $success = false;
      foreach($items as $item) {
        if($success) {
          $duplicates[] = $item['feedItem_id'];
        } else {
          $success = $this->startDownload($item, feedItem::STATUS_AUTO_DL);
        }
      }
    }

    // After matching has occured, updated item statuses
    if(count($duplicates) !== 0) // mark feedItems as duplicate if another feedItem of the same season and episode
      feedItem::model()->updateByPk($duplicates, array('status'=>feedItem::STATUS_DUPLICATE)); // has been downloaded
  }

  /**
   * looks for feedItems that matching a favoriteString in the database
   * and starts them.   Needs some work to prevent duplicate downloads
   * @param integer a feeditem status to limit the search to
   */
  public function checkStringFavorites($itemStatus = feedItem::STATUS_NEW) {
    Yii::log('Looking for string favorites', CLogger::LEVEL_ERROR);
    $db = Yii::app()->db;
    $toStart = array();

    $reader = $db->createCommand(
        'SELECT * FROM matchingFavoriteStrings'.
        '  WHERE feedItem_status='.$itemStatus)->query();

    foreach($reader as $row) {
      $this->startDownload($item, feedItem::STATUS_AUTO_DL);
    }
    
  }

  /**
   * looks for feedItems that matching a favoriteTvShow in the database
   * @param integer a feeditem status to limit the search to
   */
  public function checkTvShowFavorites($itemStatus = feedItem::STATUS_NEW) {
    Yii::log('Looking for TvShow favorites', CLogger::LEVEL_ERROR);
    $duplicates = $old = $dldEpisodes = array();
    $db = Yii::app()->db;

    // Mark any duplicate episodes
    $db->createCommand(
        'UPDATE feedItem'.
        '   SET status='.feedItem::STATUS_DUPLICATE.
        ' WHERE feedItem.status = '.$itemStatus.
        '   AND feedItem.id IN ( SELECT feedItem_id'.
                              '    FROM matchingFavoriteTvShows m'.
                              '   WHERE m.tvEpisode_status = '.tvEpisode::STATUS_DOWNLOADED.
                              ');'
    )->execute();

    // Mark any old episodes with favorite set to only newer episodes(handled inside the view)
    $db->createCommand(
        'UPDATE feedItem'.
        '   SET status='.feedItem::STATUS_OLD.
        ' WHERE feedItem.status = '.$itemStatus.
        '   AND feedItem.id IN ( SELECT feedItem_id'.
                              '    FROM onlyNewerFeedItemFilter'.
                              ')'
    )->execute();

    // get any matching items with the right itemStatus
    $season = $episode = 0;
    $reader = $db->createCommand(
        'SELECT * FROM matchingFavoriteTvShows'.
        ' WHERE feedItem_status='.$itemStatus.
        '   AND tvEpisode_status='.tvEpisode::STATUS_NEW
    )->query();

    // loop through the matching items
    // and group the duplicates by tvEpisode_id
    $toStart = array();
    foreach($reader as $row) {
      $toStart[$row['tvEpisode_id']][] = $row;
    }
    unset($row);

    foreach($toStart as $items) {
      // For now just take the first item of each tvepisode, but this
      // structure allows there to be a decision making process inserted
      // here to decide which item based on feed priority or something.
      $success = false;
      foreach($items as $item) {
        if($success) {
          $duplicates[] = $item['feedItem_id'];
        } else {
          $success = $this->startDownload($item, feedItem::STATUS_AUTO_DL);
        }
      }
    }

    // After matching has occured, updated item statuses
    if(count($duplicates) !== 0) // mark feedItems as duplicate if another feedItem of the same season and episode
      feedItem::model()->updateByPk($duplicates, array('status'=>feedItem::STATUS_DUPLICATE)); // has been downloaded
  }

}

