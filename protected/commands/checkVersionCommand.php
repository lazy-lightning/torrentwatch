<?php

class checkVersionCommand extends BaseConsoleCommand
{
  /**
   * cacheKey 
   * 
   * @var string the key used to store the last update in cache
   */
  public $cacheKey = 'application.commands.checkVersion';
  
  /**
   * lastUpdated
   * 
   * @var integer the last time the version was checked, in unix time() format
   *              loaded from cache as $this->cacheKey
   */
  protected $lastUpdated;

  /**
   * updateFrequency 
   * 
   * @var integer the time to wait between updates, in minutes defaults to 1430
   *              which is one day minus 10 minutes.
   */
  public $updateFrequency = 1430;

  protected function afterRun()
  {
    if($this->lastUpdated === false)
      Yii::app()->getCache()->add($this->cacheKey, time());
    else
      Yii::app()->getCache()->set($this->cacheKey, time());
  }

  protected function beforeRun($args)
  {
    if(false === ($this->lastUpdated = Yii::app()->getCache()->get($this->cacheKey)))
      return true;
    if($this->lastUpdated + ($this->updateFrequency*60) < time())
      return true;

    return false;
  }

  protected function checkVersion()
  {
    echo "Checking Version . . .\n";
    Yii::import('application.extensions.versionCheck');
    $v = new versionCheck;
    $v->url = 'http://nmtdvr.com/latest.php';
    $v->init();
    if(false === ($newest = $v->getNewestVersion()))
    {
      echo "System already up to date.\n";
    }
    else 
    {
      echo "New version available";
//      Yii::app()->dvrConfig->newVersion = $newest;
//      Yii::app()->dvrConfig->save();
    } 
  }

  public function run($args)
  {
    if($this->beforeRun($args))
    {
      if(Yii::app()->dvrConfig->checkNewVersion)
        $this->checkVersion();
      if(Yii::app()->dvrConfig->submitUsageLogs)
        $this->submitLogs();
      $this->afterRun();
    }
  }

  protected function submitLogs()
  {
    echo "\nSending usage logs . . .\n";
    Yii::import('application.extensions.sendLogs');
    $l = new sendLogs;
    // The categories that only log the query request, such as show tvEpisode 733, or update tvShow 412
    // or list feedItems related to movie 612
    $l->categories = array('fakeYii.init', 'application.components.BaseController');
    $l->url = 'http://nmtdvr.com/usageLogs.php';
    $l->init();
    $l->submitLogs();
    echo $l->requestResponse."\n\n";
  }

}

