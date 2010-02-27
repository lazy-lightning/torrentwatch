<?php

class sqlitePdoTest extends CTestCase
{
  public $path = '/tmp/foo.db';

  protected function getConnection()
  {
    $conn = new SqliteConnection();
    $conn->connectionString = "sqlite:".$this->path;
    $conn->init();
    return $conn;
  }

  function testSchemaChanged()
  {
    try {
      unlink($this->path);
    } catch (Exception $e) {
    }

    // Create first connection object and create table
    $conn_1 = $this->getConnection();
    $conn_1->createCommand('CREATE TABLE test1 ( key, value )')->execute();
    // Fetch list of tables using first connection
    $result = $conn_1->createCommand("SELECT name FROM sqlite_master WHERE type='table';")->queryAll();
    $this->assertEquals(1, count($result));

    // Create a second connection
    $conn_2 = $this->getConnection();
    // create table : perform a schema change operation
    $conn_1->createCommand('CREATE TABLE test2 ( key2, value2 )')->execute();

    // Fetch list of tables using first connection
    $y = $conn_1->createCommand("SELECT name FROM sqlite_master;");
    try {
      $result = $y->queryAll();
    } catch (Exception $e) {
      $result = array();
    }
    // verify we have two tables now from first connection
    $this->assertEquals(2, count($result));
  }

}
