<?php

class clientFolder extends BaseClient 
{
  public function __construct($manager) {
    parent::__construct($manager);
  }

  public function addByData($data) 
  {
    $saveIn = $this->getSaveInDirectory();

    if(!is_dir($saveIn)) {
      if(is_file($saveIn)) {
        rename($saveIn, $saveIn.'.BAK');
      }
      // mkdir can complain if it doesnt have permission
      @mkdir($saveIn, 0777, true);
    }

    if(is_dir($saveIn) && is_writable($saveIn))
    {
      $title = strtr($this->manager->title, '/', '_');
      $extension = $this->manager->downloadType == feedItem::TYPE_NZB ? 'nzb' : 'torrent';
      $filename = "$saveIn/$title.$extension";

      if(file_exists($filename)) 
      {
        for($i=0;file_exists($filename);$i++) 
        {
            $filename = "$saveIn/$title.$i.$extension";
        }
      }
  
      Yii::log(print_r($this->manager->title, TRUE), CLogger::LEVEL_ERROR);
      Yii::log("Writing $title to $filename", CLogger::LEVEL_ERROR);
      $return = file_put_contents($filename, $data);
    }
    else
      $return = False;

    if(!$return)
        $this->_error = 'Unable to write to file: $filename';
  
    return $return;
  }

  public function getClassName() 
  {
    return __CLASS__;
  }
}

