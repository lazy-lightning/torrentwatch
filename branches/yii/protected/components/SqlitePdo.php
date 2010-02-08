<?php

// based on code from http://www.yiiframework.com/doc/cookbook/38/

/**
 * the purpose of this class is two fold
 * 1) ensure that this transaction is the only transaction currently active with the database.  If another
 *    transaction is active the class will sleep for a short time and try again.  This is accomplished by 
 *    starting all transactions in immediate mode which a RESERVED lock.  Other processes may still read from the
 *    db just not within an SqlitePdo transaction.
 * 2) Allow nested transactions through the use of savepoints
 */

class SqlitePdo extends PDO
{
  /**
   * @var number The current transaction level.
   */
  protected $transLevel = 0;

  /**
   * @var number The number of times to retry beginImmediate
   */
  public $retryCount = 40;

  /**
   * @var number The time to wait between retrys in ms
   */
  public $retryTime = 250000;

  /**
   * If transaction has not been commited at this point 
   * they will be rolled back.
   */
  public function __destruct()
  {
    while($this->transLevel) {
      Yii::log('Uncommiited changes, rolling back.', CLogger::LEVEL_ERROR);
      $this->rollback();
    }
  }
  /**
   * Attempts to create a transaction with an immediate write lock on the sqlite db
   * which will prevent another transaction from aquiring a write lock.  Upon failure
   * due to another process having a write lock on the db the function will retry.
   * --
   * based on user contributes notes from php manual for PDO::beginTransaction
   * origional post by rjohnson at intepro dot us
   */
  protected function beginImmediate()
  {
    $finished=false;
    $count=0;
    while($finished===false)
    {
      try 
      {
        $this->exec("BEGIN IMMEDIATE");
        $finished = true;
      }
      catch (PdoException $e)
      {
        if($count++ <= $this->retryCount && stripos($e->getMessage(), 'DATABASE IS LOCKED') !== false) 
        {
          // This should be specific to SQLite, sleep for awhile and try again.
          usleep($this->retryTime);
        } 
        else 
        {
          throw $e;
        }
      }
    }
  }

  /**
   * Overload beginTransaction to issue {@link beginImmediate} if there
   * is not an active transaction for this PDO instance otherwise
   * create an sqlite savepoint to allow for nested transactions.
   * @param none
   */
  public function beginTransaction() 
  {
    if($this->transLevel == 0) 
    {
      $this->beginImmediate();
    }
    else
    {
      $this->exec("SAVEPOINT LEVEL{$this->transLevel}");
    }

    $this->transLevel++;
    return $this;
  }

  /**
   * Issue sqlite commit/release as required by the transaction level
   */
  public function commit() {
    $this->transLevel--;

    if($this->transLevel == 0) 
    {
      $this->exec("COMMIT");
    } 
    else
    {
      $this->exec("RELEASE SAVEPOINT LEVEL{$this->transLevel}");
    }
  }

  /**
   * Issue sqlite rollback/rollback to savepoint as required by the transaction level
   */
  public function rollBack() {
    $this->transLevel--;

    if($this->transLevel == 0) 
    {
      $this->exec("ROLLBACK");
    } 
    else 
    {
      $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transLevel}");
    }
  }
}

