<?php

// this class is kindof a mess, but its so small i dont care
class versionCheck
{
  /**
   * url 
   * 
   * @var string the url to query for latest version.  Current version will be
   *             appended as 'version' GET variable ( ?version=foo )
   */
  public $url = '';

  private $current;
  private $newest = false;

  public function init()
  {
    if(empty($this->url))
      throw new CException("URL cannot be empty");

    $this->current = $this->getCurrentVersion();
    $newest = file_get_contents($this->url."?version=".urlencode($this->current));

    if($this->validVersion($current) && $this->compareVersions($current, $newest))
      $this->newest = $newest;
  }

  protected function compareVersions($current, $newest)
  {
    return strcmp($this->current, $this->newest) === -1;
  }

  public function getCurrentVersion()
  {
    if($this->current !== null)
      return $this->current;
    if(preg_match('/version="(.*)"/', file_get_contents(basename(__FILE__).'../../appinfo.json'), $regs))
      return $regs[1];
    return false;
  }

  public function getNewestVersion()
  {
    return $this->newest;
  }

  protected function validVersion($version)
  {
    return !($version === false || $version === '$id$');
  }
}
