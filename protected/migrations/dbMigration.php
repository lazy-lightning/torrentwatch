<?php

abstract class dbMigration {
  protected $owner;
  protected $db;

  public function __construct($owner)
  {
    $this->owner = $owner;
    $this->db = Yii::app()->db;
  }

  protected function replaceView($view, $sql)
  {
    $this->db->createCommand(
        "DROP VIEW $view"
    )->execute();
    $this->db->createCommand(
        "CREATE VIEW $view AS $sql"
    )->execute();
  }

  protected function addColumn($table, $columnSql)
  {
    $this->db->createCommand(
        "ALTER TABLE $table ADD $columnSql"
    )->execute();
  }

  protected function setDbVersion($version)
  {
    $this->db->createCommand(
        "DELETE FROM version"
    )->execute();
    $this->db->createCommand(
        "INSERT INTO version (version) VALUES($version)"
    )->execute();
  }
}
