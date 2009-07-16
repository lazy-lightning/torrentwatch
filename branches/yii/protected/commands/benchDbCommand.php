<?

class benchDbCommand extends BaseConsoleCommand {
  public function run($args)
  {
    if( count($args) !== 2 ||
        !is_file($args[0]) ||
        !is_numeric($args[1]) ||
        $args[1] <= 0 )
    {
      global $argv;
      echo "\tUsage: $argv[0] $argv[1] <file containing sql query> <times to repeat>\n\n";
      return;
    }

    $timers = array();
    $count = $args[1];
    $sql = file_get_contents($args[0]);
    $command = Yii::app()->db->createCommand($sql);
    for($i = 0; $i < $count; ++$i)
    {
      $start = microtime(TRUE);
      $command->queryAll();
      $timers[] = microtime(TRUE)-$start;
    }

    $avg = 0;
    foreach($timers as $time) {
      $avg += $time;
    }
    $avg = $avg/$count;
    echo "Average runtime: $avg\n";
  }
}
