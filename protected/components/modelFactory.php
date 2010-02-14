<?php

// The factory is used to fetch items by information other than their id
// if an item doesn't exist it is created.  This is mostly used when initializing
// new feedItems and their related models

// The factory will throw CException if something fails to save to the database
// Failure to save is usually a problem with item validation
class modelFactory extends CApplicationComponent {

  /*
   * feedItemByAttributes
   *
   * @param array must contain at least 'hash' and 'title' keys 
   * @throws CException on incorrect attributes array
   * @throws CException on failure to save(validation usually)
   */
  public function feedItemByAttributes($attributes)
  {
    if(empty($attributes['hash']))
      throw new CException("No hash provided for ".__FUNCTION__);
    if(empty($attributes['title']))
      throw new CException("No title provided for ".__FUNCTION__);
    $item = feedItem::model()->find('hash = :hash', array('hash'=>$attributes['hash']));
    if($item === null)
    {
      $item = new feedItem;
      $item->attributes = $attributes;
      $details = new mediaTitleParser($item->title, $this);
      $details->applyTo($item);
      if(!$item->save())
        throw new CException("New feed item failed to save");
    }

    return $item;
  }

  public function genreByTitle($title) {
    $genre = genre::model()->find('title LIKE :title', array(':title'=>$title));
    if($genre === null) {
      $genre = new genre;
      $genre->title = $title;
      if(!$genre->save())
        throw new CException("New genre failed to save");
    }
    return $genre;
  }

  public function networkByTitle($title) {
    // Remove the preface
    if(strtolower(substr($title, 0, 4)) === 'the ')
      $title = substr($title, 4);
    // convert common differences
    $titleMap = array(
        'BBC One'=>'BBC1', 'BBC-1'=>'BBC1', 'BBC 1'=>'BBC1',
        'BBC Two'=>'BBC2', 'BBC-2'=>'BBC2', 'BBC 2'=>'BBC2',
        'ITV One'=>'ITV1', 'ITV-1'=>'ITV1', 'ITV 1'=>'ITV1',
        'Cartoon'=>'Cartoon Network',
        'History'=>'History Channel',
    );
    if(isset($titleMap[$title]))
      $title = $titleMap[$title];

    $network = network::model()->find('title LIKE :title', array(':title'=>$title));
    if($network === null) {
      $network = new network;
      $network->title = $title;
      if(!$network->save())
        throw new CException("New network failed to save");
    }
    return $network;
  }

  public function tvEpisodeByEpisode($tvShow, $season, $episode) {
    if(is_string($tvShow)) {
      $tvShow = $this->tvShowByTitle($tvShow);
    }
    $tvEpisode = tvEpisode::model()->find(array(
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

  public function tvShowByTitle($title) {
    if(empty($title)) {
      Yii::log('trying to init tvShow without title'."\n".print_r(debug_backtrace()), CLogger::LEVEL_ERROR);
      throw new CException('Attempt to initialize tvShow with no title');
    }

    $tvShow = tvShow::model()->find(array(
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

  public function qualityByTitle($title) {
    $record = quality::model()->find(array(
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

  public function qualityIdsByTitleArray($titles) {
    $ids = array();
    foreach($titles as $title)
    {
      $ids[] = $this->qualityByTitle($title)->id;
    }
    return $ids;
  }

  public function movieByImdbId($imdbId, $title) {
    $record = movie::model()->find(array(
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

  public function otherByTitle($title) {
    $record = other::model()->find(array(
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
