<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Controller {

  public $options = array();
  public $success = False;

  abstract function index();

  /* All functions not available as a web link must be prefixed with _ */

  public function __call($method, $arguments) {
    Event::run('system.404');
  }

  public function __construct() {
    if(SimpleMvc::$instance == NULL) {
      SimpleMvc::$instance = $this;
    }

    // uri to this controller, suitible for appending commands to
    $this->self_uri = SimpleMvc::$base_uri.'/'.SimpleMvc::$controller.'/';

    $this->options = $_GET;
  }

  public function __destruct() {
    SimpleMvc::log('Controller finished with status: '.$this->success ? 'Success' : 'Failure');
  }

  static public function _addModel($model) {
    static $loadedModels = array();

    if(in_array($model, $loadedModels))
      return True;

    $dir = APPPATH."/models/$model";
    if(file_exists("$dir/$model.php")) {
      ini_set('include_path', ini_get('include_path').":$dir");
      require_once("$dir/$model.php");
      $loadedModules[] = $model;
      return True;
    }
    throw new Exception('Invalid Model');
  }

  static public function _addModule($module) {
    static $loadedModules = array();
    if(in_array($module, $loadedModules))
      return True;

    $file = $module.'.php';
    $dir = MODPATH.$module.'/';
    if(file_exists(MODPATH.$module.'.php')) {
      include_once MODPATH.$module.'.php';
    } elseif(file_exists($dir.$module.'.php')) {
      ini_set('include_path', ini_get('include_path').':'.$dir);
      include_once $dir.$module.'.php';
    } elseif (dir_exists($dir)) {
      ini_set('include_path', ini_get('include_path').':'.$dir);
    } else
      return False;

    $loadedModules[] = $module;
    return True;
  }

  public static function _loadView($viewFilename, $inputData) {
    if($viewFilename =='')
      return;

    ob_start();

    // Inserts the input data into the current symbol table
    // to be used in the template
    extract($inputData, EXTR_SKIP);

    $file = $viewFilename;
    if(is_file($file))
      require $file;
    else
      throw new SimpleMvc_404_Exception($file);

    return ob_get_clean();
  }

  public function _newModel($model) {
    try {
      self::_addModel($model);
      return new $model;
    } catch(Exception $e) {
      return False;
    }
  }

} // End Controller Class

