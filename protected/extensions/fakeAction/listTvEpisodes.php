<?php

class BaseController
{
  protected function render($view, $data)
  { 
    extract($data);
    require(Yii::app()->basePath.'/../themes/classic/views/tvEpisode/'.$view.'.php');
  }
}

class tvEpisode {
  public function getEpisodeString($s=null,$e=null)
  {
    if($s === null || $e === null)
    {
      $e = $this->episode;
      $s = $this->season;
    }

    if($e > 10000)
      return $this->getEpisodeDateString($e);
    // oddly faster than sprintf
    if($s<10)
      $s = '0'.$s;
    if($e == 0)
      return "S${s}E??";
    if($e<10)
      return "S${s}E0${e}";
    return "S${s}E${e}";
  }

  protected function getEpisodeDateString($time)
  {
    static $utc = null;
    if($utc === null)
      $utc = new DateTime('Jan 1 1970', new DateTimeZone('UTC'));
    $date = clone $utc;
    $date->modify('+'.$time.' secconds');
    return $date->format('Y-m-d');
  }
}
class feedItem {
  const STATUS_NEW = 0;
  const STATUS_NOMATCH = 1;
  const STATUS_MATCH = 2;
  const STATUS_DUPLICATE = 6;
  const STATUS_OLD = 7;
  const STATUS_QUEUED = 15;
  const STATUS_FAILED_DL = 19;
  const STATUS_AUTO_DL = 20;
  const STATUS_MANUAL_DL = 21;

  /**
   * all valid statuses and their string mappings
   * @return array number=>string pairs
   */
  public static function getStatusOptions() {
    return array(
        self::STATUS_AUTO_DL=>'Automatic Download',
        self::STATUS_DUPLICATE=>'Duplicate Episode',
        self::STATUS_FAILED_DL=>'Failed Download',
        self::STATUS_NEW=>'New',
        self::STATUS_NOMATCH=>'Unmatched',
        self::STATUS_MANUAL_DL=>'Manual Download',
        self::STATUS_MATCH=>'Matched',
        self::STATUS_OLD=>'Old Episode',
        self::STATUS_QUEUED=>'Queued for User',
    );
  }

  /**
   * getStatusText
   *
   * @param integer $status
   * @return string the given status as a string
   */
  public  function getStatusText($status = null) {
    static $options = null;
    if($status === null)
      $status = $this->status;
    if($options === null)
      $options=self::getStatusOptions();
    return isset($options[$status]) ? $options[$status]
        : "unknown ({$status})";
  }

}

date_default_timezone_set(Yii::app()->getComponent('dvrConfig')->timezone);
require_once('protected/controllers/TvEpisodeController.php');
$controller = new TvEpisodeController;
$controller->actionList();
