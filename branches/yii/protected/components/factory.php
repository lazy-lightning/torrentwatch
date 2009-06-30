<?php

// The factory is used to fetch items by information other than their id
// if an item doesn't exist it is created.  This is mostly used when initializing
// new feedItems and their related models

class factory {
  public static function feedItemByAttributes($attributes)
  {
    $item = new feedItem;
    $item->attributes = $attributes;
    if(!$item->save())
      throw new CException("New feed item failed to save");

    return $item;
  }

  public static function genreByTitle($title) {
    $genre = genre::model()->find('title LIKE :title', array(':title'=>$title));
    if($genre === null) {
      $genre = new genre;
      $genre->title = $title;
      if(!$genre->save())
        throw new CException("New genre failed to save");
    }
    return $genre;
  }

  public static function networkByTitle($title) {
    // Remove the preface
    if(strtolower(substr($title, 0, 4)) === 'the ')
      $title = substr($title, 4);

    $network = network::model()->find('title LIKE :title', array(':title'=>$title));
    if($network === null) {
      $network = new network;
      $network->title = $title;
      if(!$network->save())
        throw new CException("New network failed to save");
    }
    return $network;
  }

  public static function tvEpisodeByEpisode($tvShow, $season, $episode) {
    if(is_string($tvShow)) {
      $tvShow = self::tvShowByTitle($tvShow);
    }
    $tvEpisode = tvEpisode::model()->find(array(
          'select' => 'id',
          'condition' => 'tvShow_id=:id AND season=:season AND episode=:episode',
          'params' => array(':id'=>$tvShow->id, ':season'=>$season, ':episode'=>$episode)
    ));
    if($tvEpisode === Null) {
      $tvEpisode = new tvEpisode;
      $tvEpisode->tvShow_id = $tvShow->id;
      $tvEpisode->season = $season;
      $tvEpisode->episode = $episode;
      if(!$tvEpisode->save()) {
        throw new CException('Failed to add new tvEpisode');
      }
    }
    return $tvEpisode;
  }

  public static function tvShowByTitle($title) {
    if(empty($title)) {
      Yii::log('trying to init tvShow without title'."\n".print_r(debug_backtrace()), CLogger::LEVEL_ERROR);
      throw new CException('Attempt to initialize tvShow with no title');
    }

    $tvShow = tvShow::model()->find(array(
          'select'=>'id',
          'condition' => 'title LIKE :title',
          'params' => array(':title'=>$title)
    ));
    if($tvShow === Null) {
      $tvShow = new tvShow;
      $tvShow->title = $title;
      if(!$tvShow->save()) {
        throw new CException('Failed to add new tvShow');
      }
    }
    return $tvShow;
  }

  public static function qualityByTitle($title) {
    $record = quality::model()->find(array(
          'select' => 'id',
          'condition' => 'title LIKE :quality',
          'params' => array(':quality'=>$title)
    ));
    if($record === Null) {
      $record = new quality;
      $record->title = $title;
      if(!$record->save()) {
        throw new CException('Failed to save new quality');
      }
    }
    return $record;
  }

  public static function qualityIdsByTitleArray($titles) {
    $ids = array();
    foreach($titles as $title)
    {
      $ids[] = self::qualityByTitle($title)->id;
    }
    return $ids;
  }

  public static function movieByImdbId($imdbId, $title) {
    $record = movie::model()->find(array(
          'select' => 'id',
          'condition' => 'imdbId = :imdbId',
          'params' => array(':imdbId'=>$imdbId)
    ));
    if($record === Null) {
      $record = new movie;
      $record->title = $title;
      $record->imdbId = $imdbId;
      if(!$record->save()) {
        throw new CException('Failed to save new movie');
      }
    }
    return $record;
  }

  public static function otherByTitle($title) {
    $record = other::model()->find(array(
          'select' => 'id',
          'condition' => 'title LIKE :title',
          'params' => array(':title'=>$title)
    ));
    if($record === Null) {
      $record = new other;
      $record->title = $title;
      if(!$record->save()) {
        throw new CException('Failed to save new other');
      }
    }
    return $record;
  }
}
