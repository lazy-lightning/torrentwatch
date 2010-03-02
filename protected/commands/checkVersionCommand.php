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
    // TODO: needs adding column to dvrConfig in migrations
//    if(!Yii::app()->dvrConfig->checkVersion)
//      return false;

    if(false === ($this->lastUpdated = Yii::app()->getCache()->get($this->cacheKey)))
      return true;
    if($this->lastUpdated + ($this->updateFrequency*60) < time())
      return true;

    return false;
  }

  public function run($args)
  {
    if($this->beforeRun($args))
    {
      echo "Checking Version . . .\n";
      Yii::import('application.extensions.versionCheck');
      $v = new versionCheck;
      $v->url = 'http://nmtdvr.com/latest.php';
      // What to do with the data?
      $v->init();
      if(false !==($newest = $v->getNewestVersion()))
      {
        echo "New version available";
      /* TODO: create a migration to take care of new dvrConfig value
       *       and find a non-intrusive way to inform the user.
       * Yii::app()->dvrConfig->newVersion = $newest;
       * Yii::app()->dvrConfig->save();
       */
      } else
        echo "Complete.\n";
          
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

      $this->afterRun();
    }
  }

}

