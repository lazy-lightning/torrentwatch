<?php
/**
  * This is implemented outside of the normal Yii framework due to speed considerations
  * it puts together a very basic fake Yii that is just enough the get CWebUser to tell use
  * if its authenticated or not.
  *
  * After verifying the request is from a logged in user the query is processed for directory
  * names and the directories in that directory are returned.
  */
error_reporting(E_ALL|E_STRICT);

class fakeCookies {
  private $_c, $sm;
  function __construct($sm) { $this->sm = $sm; $this->loadCookies(); }
  function itemAt($key) { return isset($this->_c[$key]) ? $this->_c[$key] : null; }
  function loadCookies() {
    foreach($_COOKIE as $name=>$value)
    {
      $value = str_replace('\"', '"', $value);
      // What is this return value for?  CWebUser doesn't recognize login if we use it
      if(($WHYvalue=$this->sm->validateData($value))!==false)
        $this->_c[$name]=(object)array('name'=>$name, 'value'=>$value);
    }
  }
}
class fakeApp {
  private $base, $cookies, $security, $state;

  function init($realBase) { 
    session_start();
    $this->base = $realBase;
    // This provides the cache used by CStatePersistor
    $this->cache = new CApcCache;
    $this->cache->init();
    // This authenticates the user cookie
    $this->security = new CSecurityManager; 
    $this->security->init();
    // This fakes the CCookieCollection so CWebUser can check the cookies
    $this->cookies = new fakeCookies($this->security);
    // CSecurityManager needs the state persister for the validation key
    $state = new CStatePersister;
    $state->init();
    $this->state = $state->load();
  }
  function __get($value) { return $this; }
  function __call($func, $args) { return $this; }

  function getCookies() { return $this->cookies; }
  function getComponent($c) { return $c==='cache'?$this->cache:null; }
  function getId() { return sprintf("%x", crc32($this->base.'NMTDVR')); }
  function getSecurityManager() { return $this->security; }
  function getGlobalState($key) { return (isset($this->state[$key]) ? $this->state[$key] : null); }
  function getRuntimePath() { return $this->base.'/runtime/'; }
}

class Yii {
  static function app() { 
    static $fake;
    if($fake===null) { $fake = new fakeApp; $fake->init(realpath(dirname(__FILE__).'/protected')); }
    return $fake;
  }
}

$yii='yii_framework/';
require_once($yii.'base/interfaces.php');
require_once($yii.'base/CComponent.php');
require_once($yii.'base/CApplicationComponent.php');
require_once($yii.'base/CSecurityManager.php');
require_once($yii.'base/CStatePersister.php');
require_once($yii.'caching/CCache.php');
require_once($yii.'caching/CApcCache.php');
require_once($yii.'web/auth/CWebUser.php');

$user = new CWebUser;
$user->allowAutoLogin = true;
$user->init();

if($user->isGuest || !isset($_GET['q'])) {
  echo "Bad user or no query";
  exit;
}
/*****************************
 *
 *  Everything above this point just ensures that the request is coming 
 *  from a logged in user
 *
 *****************************/

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
