<?php

class sendLogs 
{
  public $categories = array();
  public $logName = 'application.log';
  public $logDir = '';
  public $url = '';

  /**
    * @var browserEmulator $be
    */
  public $be;

  public $lines;

  public function compress($string)
  {
    if(function_exists('gzdeflate'))
      return gzdeflate($string);
    return $string;
  }

  public function init()
  {
    if(empty($this->url))
      throw new CException("URL cannot be empty");
    if(empty($this->categories))
      throw new CException("Categories cannot be empty");
    if(empty($this->logDir))
      $this->logDir = Yii::app()->getRuntimePath();
    if($this->be === null)
    {
      Yii::import('application.components.downloadClients.browserEmulator');
      $this->be = new browserEmulator;
    }

    $this->lines = $this->parseLogs($this->getLogs());
  }

  /**
    * Submits the log categories specified in $this->categories to $this->url via POST
    * Submits Yii::app()->getId() with the logs, which is a hash of runtime path and application name
    *   so wont identify individual users but will seperate NMT from other installations
    *
    * @var browserEmulator $be object to do the post request with
    */
  public function submitLogs()
  {
    if(empty($this->lines))
      return;

    $data = $this->compress(implode("\n", $this->lines));
    $be = $this->be;
    $be->multiPartPost = true;
    $be->addPostData('logs', array(
          'filename' => Yii::app()->getId(),
          'contents'  => $data,
    ));
    try {
      $this->requestResponse = $be->file_get_contents($this->url);
    } catch (Exception $e) {
      $this->requestResponse = 'Could not connect: '.$e->getMessage();
    }
  }

  protected function getRegExp()
  {
    $logDate = date('Y/m/d', time()-24*60*60); // yesterday
    $categories = implode('|', $this->categories);
    //            y:m:d         h:m:s     level     category       message
    return  "#^$logDate \d\d:\d\d:\d\d \[[^]]*\] \[.*(?:$categories).*\] .*#";
  }

  protected function parseLogs($logs)
  {
    $output = array();
    foreach($logs as $log)
    {
      $content = file($log);
      $regexp = $this->getRegExp();
      foreach($content as $line)
        if(preg_match($regexp, $line, $result)) {
          $output[] = $result[0];
        }
    }
    return $output;
  }

  protected function getLogs() 
  {
    $output = array();
    $len = strlen($this->logName);
    $dh = opendir($this->logDir);
    // check multiple files, incase its been rotated, or a bug causes a silly ammount of logs
    while(false!==($file = readdir($dh))) 
    {
      if(substr($file, 0, $len) !== $this->logName)
        continue;
      $tmp =$this->logDir."/$file";
      $output[] = $tmp;
    }
    closedir($dh);
    return $output;
  }
}
