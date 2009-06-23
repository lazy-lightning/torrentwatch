<?php

class pruneCacheCommand extends CConsoleCommand 
{
  public function run($args) 
  {
    $expireTime = time()-(60*60*24*3); // expire anything more than 3 days old
    $dh = opendir('cache/');
    if($dh === False) 
    {
      echo "Unable to open cache\n";
    }
    else while(False !== ($file = readdir($dh)))
    {
      if(False !== ($mtime = filemtime('cache/'.$file)))
      {
        if($mtime < $expireTime)
          unlink('cache/'.$file);
      }
    }
  }
}
