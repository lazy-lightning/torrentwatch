<?php
/**
  * This is implemented outside of the normal Yii framework due to speed considerations
  * it puts together a very basic fake Yii that is just enough the get CWebUser to tell use
  * if its authenticated or not.
  *
  * Optionally if Yii::app()->getDb() is accessed the SqliteConnection will be started and
  * createCommand() may be used.
  */
error_reporting(E_ALL|E_STRICT);

abstract class fakeYiiBase {
  static public $logs;
  static public function info() { }
  static public function log($msg='',$lvl='',$cat='') { } 
  static public function trace() { }
  static protected function getFake($framework, $config) {
    $fake = new fakeCWebApplication;
    $fake->config = include($config);
    $fake->yii = $framework;
    return $fake;
  }
}
/*
class CComponent {
}
class CApplicationComponent extends CComponent {
}
 */
class CPagination {
  public $currentPage=0;
}

class CLogger
{
  const LEVEL_TRACE='trace';
  const LEVEL_WARNING='warning';
  const LEVEL_ERROR='error';
  const LEVEL_INFO='info';
  const LEVEL_PROFILE='profile';
}

class fakeCWebApplication {
  // @var string $basePath location of the application
  public $basePath;
  // @var array $config configuration of the application
  public $config;
  // @var string $yii location of the framework
  public $yii;
  // @var boolean if user authentication required
  public $loginUrl = 'nmtdvr.php?r=site/login';
  //
  public $charset;

  private $cookies, $db, $logFile, $scriptUrl, $security, $state, $user;

  function run() {
    if(!isset($_GET['f']))
      throw new Exception(__CLASS__.' cannot handle request');

    // fakeYii only utilizes the first piece of the command (before /)
    $action = strtok($_GET['f'], '/');
    if(empty($action))
      throw new Exception('Invalid query passed to '.__CLASS__);

    $file = dirname(__FILE__)."/fakeAction/$action.php";
    if(!file_exists($file))
      throw new Exception('Invalid action passed to '.__CLASS__."\n$file");

    require($file);
  }

  function init() { 
    if(empty($this->config))
      throw new Exception('Please set config in '.__CLASS__);
    $this->basePath = realpath($this->config['basePath']);
    if(empty($this->yii))
      throw new Exception('Please set the path to yii in protected.extensions.fakeYii');

    session_start();
    $this->charset = $this->config['charset'];
    $this->yii = rtrim($this->yii, '/');

    $this->loadClasses(array($this->yii=>array(
            'base/interfaces','base/CComponent','base/CApplicationComponent','web/auth/CWebUser')));

    // finally the CWebUser we wanted
    $this->user = new CWebUser;
    $this->user->allowAutoLogin = true;
    $this->user->init();
    // only allow authenticated users in the faked application
    if($this->user->isGuest)
    {
      error_log(date('Y/m/d H:i:s')." [info] [fakeYii.init] [Denying page: {$_SERVER['QUERY_STRING']}]\n", 3, $this->basePath.'/runtime/application.log');
      $this->redirect($this->loginUrl);
      exit; // just in case
    }
    error_log(date('Y/m/d H:i:s')." [info] [fakeYii.init] [Serving page: {$_SERVER['QUERY_STRING']}]\n", 3, $this->basePath.'/runtime/application.log');
  }

  public function getDb()
  {
    if($this->db === null)
    {
      $this->loadClasses(array(
            $this->yii => array('db/CDbConnection','db/CDbCommand','db/CDbDataReader'),
            $this->basePath => array('components/SqliteCommand','components/SqlitePdo','components/SqliteConnection')));

      if(!isset($this->config['components']['db']))
        throw new Exception('No db configuration found');

      $config = $this->config['components']['db'];
      $class = isset($config['class'])?$config['class']:'CDbConnection';
      unset($config['class']);

      $this->db = new $class;
      foreach($config as $key=>$value)
        $this->db->$key = $value;
      $this->db->init();
    }
    return $this->db;
  }

  // fake get/call of functions we dont care about
  function __get($value) { return $this->getComponent($value); }
  function __call($func, $args) { return $this; }

  // return the pieces we do care about
  function getComponent($c) {
    switch($c) {
      case 'cache':
        return $this->cache;
      case 'db':
        return $this->getDb();
      case 'dvrConfig';
        return $this->getDvrConfig();
      default:
        return $this;
    }
  }
  function getDvrConfig() {
    static $config;
    if($config===null)
    {
      $this->loadClasses(array($this->yii=>array('base/CModel'),$this->basePath=>array('components/dvrConfig')));
      $config=new dvrConfig;
      $config->init();
    }
    return $config;
  }

  function getId() { return sprintf("%x", crc32($this->basePath.$this->config['name'])); }
  function getRuntimePath() { return $this->basePath.'/runtime/'; }
  function getUser() { return $this->user; }

  // Direct copy from CHttpRequest
  public function getScriptUrl()
  {
    if($this->scriptUrl===null)
    {
      $scriptName=basename($_SERVER['SCRIPT_FILENAME']);
      if(basename($_SERVER['SCRIPT_NAME'])===$scriptName)
        $this->scriptUrl=$_SERVER['SCRIPT_NAME'];
      else if(basename($_SERVER['PHP_SELF'])===$scriptName)
        $this->scriptUrl=$_SERVER['PHP_SELF'];
      else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME'])===$scriptName)
        $this->scriptUrl=$_SERVER['ORIG_SCRIPT_NAME'];
      else if(($pos=strpos($_SERVER['PHP_SELF'],'/'.$scriptName))!==false)
        $this->scriptUrl=substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
      else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT'])===0)
        $this->scriptUrl=str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
      else
        throw new CException(Yii::t('yii','CHttpRequest is unable to determine the entry script URL.'));
    }
    return $this->scriptUrl;
  }

  function loadClasses($a) { foreach($a as $b=>$c) { foreach($c as $d) { require_once("$b/$d.php"); } } }
  function redirect($url) { header('Location: '.$url); exit; }

}
