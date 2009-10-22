<?php

// the purpose of this class is to allow instantiating SqlitePDO instead of PDO
// in CDbConnection

class SqliteConnection extends CDbConnection
{
  /**
   * @var string the class to instantiate for pdo
   */
  public $pdoClass = 'SqlitePdo';

  /**
   * private in parent class so re-implemented
   * FIXME: not guaranteed future friendly.
   */
  protected $_attributes=array();

  /**
   * Overloaded to allow using custom pdo class
   *
   **
   * Creates the PDO instance.
   * When some functionalities are missing in the pdo driver, we may use
   * an adapter class to provides them.
   * @return PDO the pdo instance
   * @since 1.0.4
   */
  protected function createPdoInstance()
  {
    $pdoClass=$this->pdoClass;
    return new $pdoClass($this->connectionString,$this->username,
                  $this->password,$this->_attributes);
  }

  /**
   * private _attributes required reimplementation
   * FIXME: not guaranteed future friendly.
   **
   * Sets an attribute on the database connection.
   * @param int the attribute to be set
   * @param mixed the attribute value
   * @see http://www.php.net/manual/en/function.PDO-setAttribute.php
   */
  public function setAttribute($name,$value)
  {
    if($this->_pdo instanceof PDO)
      $this->_pdo->setAttribute($name,$value);
    else
      $this->_attributes[$name]=$value;
  }

}
