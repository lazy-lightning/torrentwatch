<?php
/**
  * This is implemented outside of the normal Yii framework due to speed considerations
  * it puts together a very basic fake Yii that is just enough the get CWebUser to tell us
  * if its authenticated or not based on cookies.
  *
  * After verifying the request is from a logged in user the query is processed for directory
  * names and the directories in that directory are returned.
  */
error_reporting(E_ALL|E_STRICT);
require_once('protected/extensions/fakeCWebApplication.php');


class Yii {
  static function app() { 
    static $fake;
    if($fake===null) { $fake = new fakeCWebApplication; $fake->init(realpath(dirname(__FILE__).'/protected')); }
    return $fake;
  }
}

if(Yii::app()->getUser()->isGuest || !isset($_GET['q'])) {
  echo "Bad user or no query";
  exit;
}

$dir = $_GET['q'];
$limit = isset($_GET['limit']) ? $_GET['limit'] : 150;

if(!is_dir($dir))
  $dir = substr($dir, 0, strrpos($dir, '/'));
if(!is_dir($dir)) {
  echo "&nbsp;Invalid Directory";
  exit;
}

$dh = opendir($dir);
$n = 0;
$out = array();
while(false !== ($file = readdir($dh)))
{
  if($file[0] === '.')
    continue;
  $path = rtrim($dir, '/').'/'.$file;
  if(is_dir($path))
    $out[$n++] = $path;
  if($n >= $limit)
    break;
}
if(count($out))
  echo implode("\n", $out);
else
  echo "No Results Found";
