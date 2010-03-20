<?php

class migrateCommand extends BaseConsoleCommand
{
  /**
   * migrations 
   * 
   * @var array migration classes (version => class)
   */
  protected $migrations = array(
  // db version => class to migrate to next version
  // FIXME: unimaginative class names
      0 => 'migrateFromVersionZero',
      1 => 'migrateFromVersionOne',
      2 => 'migrateFromVersionTwo',
      3 => 'migrateFromVersionThree',
      4 => 'migrateFromVersionFour',
      5 => 'migrateFromVersionFive',
  );

  // get version of database.  if version table doesnt exist create
  // it and set version to 0
  public function getDbVersion()
  {
    try {
      $version = Yii::app()->db->createCommand(
          'SELECT version FROM version'
      )->queryScalar();
    } catch (Exception $e) {
      $cmds = array(
          'CREATE TABLE version ( version INTEGER )',
          'INSERT INTO version (version) VALUES (0)'
      );
      foreach($cmds as $sql)
        Yii::app()->db->createCommand($sql)->execute();
      $version = 0;
    }
    return $version;
  }

  public function migrateWith($class)
  {
    $transaction = Yii::app()->db->beginTransaction();
    try {
      $migrate = new $class($this);
      $migrate->run();
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      throw $e;
    }
  }

  public function run($args)
  {
    Yii::import('application.migrations.*');
    $version = $this->getDbVersion();
    echo "Current db version: $version\n";
    while(isset($this->migrations[$version]))
    {
      $this->migrateWith($this->migrations[$version]);
      $version = $this->getDbVersion();
      echo "Migrated to version $version\n";
    }
  }
}


