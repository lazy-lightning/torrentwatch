<?php

abstract class BaseAdapterCommand extends BaseConsoleCommand
{
  public $flags = array(
      'db'      => array(
        'type' => 'component', 
        'help' => 'A valid component of Yii::app() to use as the database',
      ),
      'factory' => array(
        'type' => 'component',
        'help' => 'A valid component of Yii::app() to use as the factory',
      ),
  );

  public $adapters = array();

  public $examples = '';
  public $db;
  public $factory;
  public $flush = 0;

  protected $command;

  abstract protected function createRunner($config);

  protected function applyComponentFlag($flag, $data)
  {
    if(($component = Yii::app()->getComponent($data)))
      $this->$flag = $component;
    else
      $this->badFlag($flag, "Component not found : $data");
  }

  protected function applyIntegerFlag($flag, $data)
  {
    if(is_numeric($data))
      $this->$flag = (int)$data;
    else
      $this->badFlag($flag, "Input must be an integer : $data");
  }

  protected function applyToggleFlag($flag, $allow)
  {
    if($allow)
      $this->$flag = !$this->$flag;
    else
      $this->badFlag($flag, "Toggle only, the '=' is invalid in this context");
  }

  protected function badCommand($args, $index, $adapters)
  {
    echo "Invalid command: {$args[$index]}\n  Valid commands:\n"
         .$this->getCommandTree($adapters)."\n\n";
    exit;
  }

  protected function badFlag($flag, $reason='')
  {
    echo "Invalid flag : $flag\n  $reason\n";
    $this->getHelp();
    exit;
  }
  
  protected function getCommandTree($commands, $spacer = '    ')
  {
    $out = array();
    foreach($commands as $name => $data)
    {
      $out[] = "$spacer$name";
      if(is_array($data) && !isset($data['class']))
        $out[] = $this->getCommandTree($data, $spacer.'  ');
    }
    return implode("\n", $out);
  }

  public function getFlagsLongText()
  {
    $text = array();
    foreach($this->flags as $flag => $config)
    {
      if($config['type'] === 'toggle')
        $text[] = "  -{$flag}";
      else
        $text[] = "  -{$flag}={$config['type']}";
      $text[] = "    ".wordwrap($config['help'], 71, "\n    ");
    }
    return implode("\n", $text);
  }

  public function getFlagsShortText()
  {
    $text = array();
    foreach($this->flags as $flag => $config)
    {
      $text[] = '-'.$flag.($config['type'] === 'toggle'?'':'=');
    }
    return '['.implode('|', $text).']';
  }

  public function getHelp() 
  {
    $commandTree = $this->getCommandTree($this->adapters);
    $flagLong = $this->getFlagsLongText();
    $flagShort = $this->getFlagsShortText();
    echo <<<EOD
USAGE
  {$_SERVER['argv'][1]} $flagShort all|GROUP [SUBGROUP] ...

GROUPS
$commandTree
  
OPTIONS 
$flagLong

EXAMPLES
{$this->examples}

EOD;
  }

  protected function getAdapters($args)
  {
    $this->command = '';
    $adapters = $this->adapters;
    foreach($args as $n => $index)
    {
      $this->command .= '.'.$index;
      $index = strtolower($index);
      if($index === 'all')
        break;
      elseif(!isset($adapters[$index]))
        $this->badCommand($args, $n, $adapters);
      else
      {
        $adapters = $adapters[$index];
      }
    }
    $this->command = ltrim($this->command, '.');
    return $adapters;
  }

  protected function parseFlags($args)
  {
    while(count($args))
    {
      if($args[0][0] !== '-')
        break;
      $flag = ltrim(array_shift($args), '-');
      if(false !== strpos($flag, '='))
        list($flag, $data) = explode('=', $flag, 2);

      if(!isset($this->flags[$flag]))
        $this->badFlag($flag, 'Unknown flag');
      if($this->flags[$flag] === 'toggle')
        $this->applyToggleFlag($flag, !isset($data));
      else
      {
        if(!isset($data))
          $data = array_shift($args);
        if($this->flags[$flag]['type'] === 'component')
          $this->applyComponentFlag($flag, $data);
        elseif($this->flags[$flag]['type'] === 'integer')
          $this->applyIntegerFlag($flag, $data);
        else
        {
          echo "This flag has not been properly configured: -$flag\n";
          var_dump($this->flags[$flag]);
          exit;
        }
      }
    }
    return $args;
  }

  public function run($args)
  {
    try {
      $this->db = Yii::app()->getDb();
      $this->factory = Yii::app()->modelFactory;
      $args = $this->parseFlags($args);
      if(count($args) === 0)
        $this->getHelp();
      else
      {
        $adapters = $this->getAdapters($args);
        echo "\nCommand issued: {$this->command}\n\n";
        $this->runAdapters($adapters);
      }
    }
    catch (Exception $e) {
      echo('FATAL ERROR: '.$e->getMessage());
    }
  }

  protected function runAdapters($adapters)
  {
    if(isset($adapters['class']))
      $adapters = array($adapters);
    foreach($adapters as $name => $config)
    {
      if(is_array($config) && !isset($config['class']))
        $this->runAdapters($config);
      elseif(!isset($config['enabled']) || $config['enabled'])
      {
        if(is_array($config))
        {
          echo "Running adapter: {$config['class']}\n";
          unset($config['enabled']);
        }
        else
          echo "Running adapter: $config\n";
        try {
          $runner = $this->createRunner($config);
          $runner->run();
        } catch (Exception $e) {
          $class = is_array($config) ? $config['class'] : $config;
          echo "\n\nADAPTER ERROR: $class : ".$e->getMessage()."\n\n";
        }
      }
    }
  }

}

