<?php

abstract class dbMigration {
  protected $owner;
  protected $db;

  public function __construct($owner)
  {
    $this->owner = $owner;
    $this->db = Yii::app()->db;
  }

  /**
   * replaceView 
   * 
   * @param mixed $view the name of the view
   * @param string $sql the select query that the view will contain
   * @return void
   */
  protected function replaceView($view, $sql)
  {
    $this->db->createCommand(
        "DROP VIEW IF EXISTS $view"
    )->execute();
    $this->db->createCommand(
        "CREATE VIEW $view AS $sql"
    )->execute();
  }

  /**
   * addColumn 
   * 
   * @param string $table the table to alter
   * @param string $columnSql a column definition, such as 'foobar INTEGER NOT NULL'
   * @return void
   */
  protected function addColumn($table, $columnSql)
  {
    try 
    {
      $this->db->createCommand("ALTER TABLE $table ADD $columnSql")->execute();
    } 
    catch (CDbException $e) {
      // ignore if duplicate column error, because column already exists
      if(false === strpos($e->getMessage(), 'duplicate column name'))
        throw $e;
    }
  }

  /**
   * setDbVersion 
   * 
   * @param mixed $version the version to set the DB to
   * @return void
   */
  protected function setDbVersion($version)
  {
    $this->db->createCommand(
        "DELETE FROM version"
    )->execute();
    $this->db->createCommand(
        "INSERT INTO version (version) VALUES($version)"
    )->execute();
  }

  /**
   * recreateTable 
   * 
   * @param string $table the table to recreate
   * @param string $columnDef the column definitions for the table
   * @param array $columns strings signifying the columns to transfer from old
   *                       table.  'foo AS bar' if foo changed name to bar, otherwise
   *                       'foo' if name is the same
   * @return void
   */
  protected function recreateTable($table, $columnDef, $columns)
  {
    $tempTable = "\"__temp__$table\"";
    $table = "\"$table\"";
    $sqlColumns = '"'.implode('", "', $columns).'"';
    $commands = array(
      "ALTER TABLE $table RENAME TO $tempTable",
      "CREATE TABLE $table (\n$columnDef\n);",
      "INSERT INTO $table SELECT $sqlColumns FROM $tempTable",
      "DROP TABLE $tempTable",
    );
    foreach($commands as $sql)
    {
      $this->db->createCommand($sql)->execute();
    }
  }
}
