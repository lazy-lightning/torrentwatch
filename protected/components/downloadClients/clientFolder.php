<?php

class clientFolder extends BaseClient 
{
  public function __construct($manager) {
    parent::__construct($manager);
  }

  public function addByData($data) 
  {
    $saveIn = $this->getSaveInDirectory();
    $title = strtr('/', '_', $this->manager->title);

    $extension = $this->manager->downloadType == feedItem::TYPE_NZB ? 'nzb' : 'torrent';

    $filename = "$saveIn/$title.$extension";

    if(file_exists($filename)) 
    {
      for($i=0;file_exists($filename);$i++) 
      {
          $filename = "$saveIn/$title.$i.$extension";
      }
    }

    $return = file_put_contents($filename, $data);
    if(!$return)
        $this->_error = 'Unable to write to file: $filename';

    return $return;
  }

  public function getClassName() 
  {
    return __CLASS__;
  }
}

