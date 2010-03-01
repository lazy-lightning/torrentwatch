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

class fakeCCookieCollection {
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
class fakeCWebApplication {
  private $base, $cookies, $security, $state, $user;

  function init($realBase) { 
    $yii='yii_framework/';
    require_once($yii.'base/interfaces.php');
    require_once($yii.'base/CComponent.php');
    require_once($yii.'base/CApplicationComponent.php');
    require_once($yii.'base/CSecurityManager.php');
    require_once($yii.'base/CStatePersister.php');
    require_once($yii.'caching/CCache.php');
    require_once($yii.'caching/CApcCache.php');
    require_once($yii.'web/auth/CWebUser.php');

    session_start();
    $this->base = $realBase;
    // This provides the cache used by CStatePersistor
    $this->cache = new CApcCache;
    $this->cache->init();
    // This authenticates the user cookie
    $this->security = new CSecurityManager; 
    $this->security->init();
    // This fakes the CCookieCollection so CWebUser can check the cookies
    $this->cookies = new fakeCCookieCollection($this->security);
    // CSecurityManager needs the state persister for the validation key
    $state = new CStatePersister;
    $state->init();
    $this->state = $state->load();
    // finally the CWebUser we wanted
    $this->user = new CWebUser;
    $this->user->allowAutoLogin = true;
    $this->user->init();
  }

  // fake get/call of functions we dont care about
  // This enables Yii::app()-getRequest()->getCookies() and similar to fall through
  function __get($value) { return $this; }
  function __call($func, $args) { return $this; }

  // return the pieces we do care about
  function getCookies() { return $this->cookies; }
  function getComponent($c) { return $c==='cache'?$this->cache:null; }
  function getGlobalState($key) { return (isset($this->state[$key]) ? $this->state[$key] : null); }
  function getId() { return sprintf("%x", crc32($this->base.'NMTDVR')); }
  function getSecurityManager() { return $this->security; }
  function getRuntimePath() { return $this->base.'/runtime/'; }
  function getUser() { return $this->user; }
}

