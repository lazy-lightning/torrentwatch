<?php

class scanOthersCommand extends BaseConsoleCommand {

  public function run($args) {
    $this->scanForMovies();
  }

  protected function scanForMovies() {
    $db = Yii::app()->db;
    $now = time();
    $scanned = array();
    $reader = $db->createCommand('SELECT id, title'.
                                 '  FROM other'.
                                 ' WHERE lastImdbUpdate = 0' // one shot

    )->query();
    foreach($reader as $row) {
      $scanned[] = $row['id'];

      $results = IMDbFetch::find($row['title']);
      if(empty($results)) {
        continue;
      }

      echo "Search for: ".$row['title']."\tResult: ".$results[0]['title']."\n";
      continue;

      // Delete other and recreate as movie
      $movie = factory::movieByTitle($results[0]['title']);
      if($movie->isNewRecord)
        $movie->save();

      $db->createCommand(
          'UPDATE feedItem '.
          '   SET movie_id='.$movie->id.', other_id=NULL'.
          ' WHERE other_id='.$row['id']
      )->execute();
      other::model()->deleteByPk($row['id']);
    }
//    other::model()->updateByPk($scanned, array('lastImdbUpdate'=>$now));
  }
}

