<?php defined('SYSPATH') or die('No direct access allowed');

final class SimpleMvc {
  public static $instance;

  // Routing
  public static $base_uri; // web path to front controller
  public static $current_uri; // requested route
  public static $query_string;
  public static $complete_uri; // last 2 combined
  public static $controller;
  public static $method;
  public static $arguments = array();

  // Output Buffering
  public static $bufferLevel;
  public static $output;

  // Storage of the logging messages
  public static $log;

  // Possible include paths
  public static $includePaths;

  public static $has_error;

  public static function setup() {
    static $run;

    if($run === TRUE)
      return;

    Benchmark::start('environment_setup');

    self::$includePaths = array(APPPATH, SYSPATH);

    ob_start(array(__CLASS__, 'output_buffer'));

    self::$bufferLevel = ob_get_level();

    //set_error_handler(array(__CLASS__, 'exceptionHandler'));
    set_exception_handler(array(__CLASS__, 'exceptionHandler'));

    header('Content-Type: text/html; charset=UTF-8');

    Event::add('system.routing', array(__CLASS__, 'findUri'));
    Event::add('system.routing', array(__CLASS__, 'initRouting'));

    Event::add('system.execute', array(__CLASS__, 'instance'));

    Event::add('system.404', array(__CLASS__, 'show_404'));

    Event::add('system.shutdown', array(__CLASS__, 'shutdown'));
    Event::add('system.shutdown', array(__CLASS__, 'saveLog'));

    $run = TRUE;

    Benchmark::stop('environment_setup');
  }

  public static function findUri() {
    // The front controller
    self::$base_uri = $_SERVER['SCRIPT_NAME'];

    // Requested Route
    if(isset($_SERVER['PATH_INFO']) AND $_SERVER['PATH_INFO']) {
      self::$current_uri = $_SERVER['PATH_INFO'];
    } elseif (isset($_SERVER['ORIG_PATH_INFO']) AND $_SERVER['ORIG_PATH_INFO']) {
      self::$current_uri = $_SERVER['ORIG_PATH_INFO'];
    } elseif (isset($_SERVER['PHP_SELF']) AND $_SERVER['PHP_SELF']) {
      self::$current_uri = $_SERVER['PHP_SELF'];
    }

    if(isset($_SERVER['SCRIPT_FILENAME'])) {
      // The front controller directory and filename
      $fc = substr(realpath($_SERVER['SCRIPT_FILENAME']), strlen(DOCROOT));

      if (($strpos_fc = strpos(self::$current_uri, $fc)) !== FALSE) {
        // Remove the front controller from the current uri
        self::$current_uri = substr(self::$current_uri, $strpos_fc + strlen($fc));
        if(self::$current_uri === False)
          self::$current_uri = '';
      }
    }

    // If no uri use the default or 404
    if(self::$current_uri === '' OR self::$current_uri === '/') {

      if(defined('DEFAULT_CONTROLLER'))
        throw new SimpleMvc_301_Exception(DEFAULT_CONTROLLER);

      Event::run('system.404');
    }

    // Clean up any double slashes
    self::$current_uri = preg_replace('#//+#', '/', self::$current_uri);

  }

  public static function initRouting() {
    if(!empty($_SERVER['QUERY_STRING'])) {
      self::$query_string = '?'.trim($_SERVER['QUERY_STRING'], '&/');
    }

    self::$complete_uri = self::$current_uri.self::$query_string;
    $segments = explode('/', trim(self::$current_uri, '/'));
    self::$controller = $segments[0];

    // Redirect to prevent accessing controllers without trailing slash
    if(!isset($segments[1]) && substr(self::$current_uri, -1) !== '/') {
      throw new SimpleMvc_301_Exception(self::$controller);
    }

    self::$method = isset($segments[1]) ? $segments[1] : 'index';
    if(isset($segments[2]))
      self::$arguments = array_slice($segments, 2);

  }

  // This function is called as part of system.ready
  // It will load the controller and execute the route
  public static function & instance() {
    if(self::$instance === NULL) {
      if(self::$method[0] === '_') {
        Event::run('system.404');
      }
       
      Benchmark::start('controller_setup');
      try {
        // Include the base controller class
        require_once SYSPATH.'Controller.php';

        // Include the specified controller class
        require_once APPPATH.'controllers/'.self::$controller.'.php';
        $class = self::$controller."Controller";
        $controller = new $class();
      } catch(Exception $e) {
          // Controller Failed
        Event::run('system.404');
      }
      Benchmark::stop('controller_setup');
      Benchmark::start('controller_execution');

      $method = self::$method;

      // Uglyness below
      $append = '';
      if(count(self::$arguments) !== 0) {
        $append = 'with command: '.implode('/', self::$arguments);
      }
      if(count($_GET) !== 0) {
        $append .= "\nOptions: ".print_r($_GET, TRUE);
      }
      self::log("Calling $class -> $method $append");
      // end Ugly

      try {
        $controller->$method(self::$arguments);
      } catch(Exception $e) {
        Event::run('system.404');
      }
      Event::run('system.post_controller');

      Benchmark::stop('controller_execution');
    }

    return self::$instance;
  }

  public static function log($msg) {
    if(!empty($msg)) {
      if(is_string($msg))
        self::$log[] = $msg;
      else
        self::$log[] = print_r($msg, TRUE);
    }
    // Catch post-shutdown logging
    if(Event::has_run('system.shutdown')) {
      self::saveLog();
    }
  }

  public static function saveLog() {
    $logfile=USERPATH.self::$controller.'.'.date('y.m.d').'.log';

    $count = 0;
    $output = array();

    if(!Event::has_run('system.shutdown'))
      $output[] = "\nRequest Time: ".date("D M j G:i:s T Y");

    $lastmsg = '';
    foreach(self::$log as $msg) {
      if($lastmsg == $msg) {
        $count++;
        continue;
      } elseif($count) {
        $output[] = "  $count more times\n";
        $count = 0;
      }

      $lastmsg = $msg;
      $output[] = rtrim($msg, "\n");
    }
    
    file_put_contents($logfile, implode("\n",$output)."\n", FILE_APPEND);
    self::$log = array();
  }

  public static function output_buffer($output) {
    if(!Event::has_run('system.send_headers'))
      Event::run('system.send_headers');

    self::$output = $output;

    return $output;
  }

  public static function close_buffers($flush = True){
    if(ob_get_level() >= self::$bufferLevel) {
      $close = ($flush === True) ? 'ob_end_flush' : 'ob_end_clean';

      while(ob_get_level() > self::$bufferLevel) {
        $close();
      }

      ob_end_clean();

      self::$bufferLevel = ob_get_level();
    }
  }

  public static function shutdown() {
    self::close_buffers(TRUE);

    Event::run('system.display', self::$output);

    echo self::$output;
  }

  public static function show_404($page = False, $template = False) {
    throw new SimpleMvc_404_Exception($page, $template);
  }

  public static function find_file($directory, $filename, $required = False, $ext = False) {
    if($ext == '')
      $ext = '.php';
    else
      $ext = '.'.$ext;

    $search = $directory.'/'.$filename.$ext;
    foreach(self::$includePaths as $path) {
      if(is_file($path.$search))
        return $path.$search;
    }

    if($required)
      throw new SimpleMvcException("Resource Not Found: $directory/$filename", $directory, $filename);

    return False;
  }

  public static function exceptionHandler($exception, $message = NULL, $file = NULL, $line = NULL) {
    // PHP errors have 5 args, always
    $PHP_ERROR = (func_num_args() === 5);

    if($PHP_ERROR && (error_reporting() & $exception) === 0)
      return;

    self::$has_error = True;

    if($PHP_ERROR) {
      $code = $exception;
      $type = 'PHP Error';
      $template = 'simplemvc_error_page';
    } else {
      $code = $exception->getCode();
      $type = get_class($exception);
      $message = $exception->getMessage();
      $file = $exception->getFile();
      $line = $exception->getLine();
      $template = ($exception instanceof SimpleMvcException) ? $exception->getTemplate() : 'simplemvc_error_page';
    }

    if(!headers_sent()) {
      if($PHP_ERROR) {
        header('HTTP/1.1 500 Internal Server Error');
      } elseif(method_exists($exception, 'sendHeaders')) {
        $exception->sendHeaders();
      }
    }

    while(ob_get_level() > self::$bufferLevel) {
      ob_end_clean();
    }

    // Include the error
    $file = self::find_file('views', $template);
    if($file)
      require $file;
    else
      require SYSPATH.'views/simplemvc_error_page.php';

    if(!Event::has_run('system.shutdown')) {
      Event::run('system.shutdown');
    }

    error_reporting(0);
    exit;
  }
} // End SimpleMvc Class

class SimpleMvcException extends Exception {

  protected $template = 'simplemvc_error_page';

  protected $header = False;

  public function __construct($error) {
    $message = 'Unkown Exception: '.$error;
    parent::__construct($message);
  }

  public function __toString() {
    return (string) $this->message;
  }

  public function getTemplate() {
    return $this->template;
  }

  public function sendHeaders() {
    header('HTTP/1.1 500 Internal Server Error');
  }
}

class SimpleMvc_404_Exception extends SimpleMvcException {
  public function __construct($page = False, $template = False) {
    if($page === False) {
      $page = SimpleMvc::$base_uri.'/'.SimpleMvc::$complete_uri;
    }
    echo '<pre>'; 
    print_r(debug_backtrace());
    echo '</pre>'; 

    Exception::__construct("Page Not Found: $page\n<pre>$trace</pre>");
    if($template)
      $this->template = $template;
  }

  public function sendHeaders() {
    header('HTTP/1.1 404 File Not Found');
  }
}

class SimpleMvc_301_Exception extends SimpleMvcException {
  public function __construct($controller, $method = '') {
    $this->url = SimpleMvc::$base_uri.'/'.$controller.'/'.$method;
    $this->template = '';
    Exception::__construct('foobar');
  }

  public function sendHeaders() {
    header("Location: {$this->url}", TRUE, 301);
  }
}

function __autoload($class) {
  require_once("$class.php");
}
