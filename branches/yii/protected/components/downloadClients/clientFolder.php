<?php

class clientFolder extends clientExecutable {

  public function addByData($data, $opts) {
    $saveIn = $this->getSaveInDirectory($opts);
    $title = $this->getTitle($opts);
    $extension = Yii::app()->dvrConfig->fileExtension;

    $filename = $orig = $saveIn.'/'.$title.'.'.$extension;

    $i = 0;
    while(file_exists($filename)) {
        $filename = $orig.'.'.++$i;
    }
    Yii::log("Saving $title to $filename", CLogger::LEVEL_ERROR);
    return file_put_contents($filename, $data);
  }
}

