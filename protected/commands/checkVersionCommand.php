<?php

class checkVersionCommand extends BaseConsoleCommand
{
  /**
   * url 
   * 
   * @var string the url to query for latest version.  Current version will be
   *             appended as 'version' GET variable ( ?version=foo )
   */
  public $url = 'http://nmtdvr.com/latest.php';

  /**
   * cacheKey 
   * 
   * @var string the key used to store the last update in cache
   */
  public $cacheKey = 'application.commands.checkVersion';
  
  /**
   * updateFrequency 
   * 
   * @var integer the time to wait between updates, in minutes defaults to 1430
   *              which is one day minus 10 minutes.
   */
  public $updateFrequency = 1430;

  /**
   * lastUpdated
   * 
   * @var integer the last time the version was checked, in unix time() format
   *              loaded from cache as $this->cacheKey
   */
  protected $lastUpdated = 0;

  public function run($args)
  {
    if($this->beforeRun($args))
    {
      $this->checkVersion();
      $this->afterRun();
    }
  }

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

    $this->lastUpdated = Yii::app()->getCache()->get($this->cacheKey);

    if($this->lastUpdated === false)
      return true;
    if($this->lastUpdated + ($this->updateFrequency*60) < time())
      return true;

    return false;
  }

  protected function checkVersion()
  {
    $version = $this->getVersion();
    $newest = file_get_contents($this->url."?version=".urlencode($version));

    if($version === false || $version === '$id')
    {
      echo 'You are using an undefined version of NMTDVR.  No information available';
      return;
    }

    switch(strcmp($version, $newest))
    {
      case -1:
        $this->newVersion($newest);
        break;
      case 0:
        echo "You are up to date: $version\n";
        break;
      case 1:
        echo "Your version is newer than available, odd\n";
        break;
      default:
        echo "strcmp returned odd value\n";
        break;
    }
  }

  protected function getVersion()
  {
    if(preg_match('/version="(.*)"/', file_get_contents(basename(__FILE__).'../../appinfo.json'), $regs))
      return $regs[1];
    return false;
  }

  protected function newVersion($version)
  {
    // stub for now, should setup to inform user in a non-invasive manner
    echo "New Version Available: $version\n";
  }

}
