<?php

abstract class BaseDvrConfig extends CAttributeCollection {
  protected $_changed;
  protected $_id = null;

  public function __construct() {
    $this->caseSensitive = true;
  }

  public function init() {
    $this->_changed = array();
  }

  public function add($key, $value) {
    parent::add($key, $value);
    $this->_changed[$key] = true;
  }

  public function setChanged($key) {
    $this->_changed[$key] = true;
  }

  public function save() {
    $key = $value = 0;
    $sql = 'UPDATE dvrConfig SET value = :value WHERE key = :key AND dvrConfigCategory_id';
    if($this->_id === null)
      $sql .= ' IS NULL';
    else
      $sql .= ' = :catId';

    $cmd = Yii::app()->db->createCommand($sql);
    $cmd->bindParam(':key', $key);
    $cmd->bindParam(':value', $value);
    if($this->_id !== null)
      $cmd->bindValue(':catId', $this->_id);

    foreach($this->_changed as $key => $foo) {
      $value = $this->$key;
      if(is_object($value))
        $value->save();
      else {
        Yii::log("update dvrConfig set value = $value where key = $key and dvrConfigCategory_id = ".($this->_id === null ? 'null' : $this->_id), CLogger::LEVEL_ERROR);
        $cmd->execute();
      }
    }
    $this->_changed = array();
  }

  // attribute label functions copied from CModel since no multi-inheritance
  // allows this to be used like an AR class from the view
  public function hasErrors() {
    return False;
  }

  public function attributeLabels() {
    return array();
  }

  public function getAttributeLabel($attribute)
  {
    $labels=$this->attributeLabels();
    if(isset($labels[$attribute]))
      return $labels[$attribute];
    else
      return $this->generateAttributeLabel($attribute);
  }

  public function generateAttributeLabel($name)
  {
    return ucwords(trim(strtolower(str_replace(array('-','_'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
  }
}

class dvrConfigCategory extends BaseDvrConfig {
  private $_parent;
  private $_title;

  /**
   *
   * @param dvrConfig the parent dvrClass instantiating this class
   * @param string the title of this category
   * @param array an array of key=>value pairs to fill the array
   */
  public function __construct($parent, $id, $title, $values) {
    parent::__construct();
    $this->_parent = $parent;
    $this->_title = $title;
    $this->_id = $id;
    foreach($values as $row) {
      $this->add($row['key'], $row['value']);
    }
  }

  /**
   * Notifies parent of any add events to propogate save
   * @param mixed the key to be added
   * @param mixed the value to be associated with said key
   */
  public function add($key, $value) {
    parent::add($key, $value);
    $this->_parent->setChanged($this->_title);
  }

  public function init() {
    parent::init();
    $this->_parent->add($this->_title, $this);
  }

  public function getTitle() {
    return $this->_title;
  }

}

class dvrConfig extends BaseDvrConfig {

  public function init() {
    $db = Yii::app()->db;
    // Get our configuration information out of the database
    $reader = Yii::app()->db->createCommand(
        "SELECT key,value,dvrConfigCategory_id FROM dvrConfig"
    )->query();
    // add anything not in a group to the main config, organize anything else into groups to be added
    $data = array();
    foreach($reader as $row) {
      if($row['dvrConfigCategory_id'] === null) 
        $this->add($row['key'], $row['value']);
      else {
        $data[$row['dvrConfigCategory_id']][] = $row;
      }
    }
    // get all the category names
    $reader = $db->createCommand(
        "SELECT id, title FROM dvrConfigCategory"
    )->query();
    // loop through and create categories
    foreach($reader as $row) {
      $id = $row['id'];
      $title = $row['title'];
      $c = new dvrConfigCategory($this, $id, $title, empty($data[$id]) ? array() : $data[$id]);
      // dvrConfigCategory will add to parent on successfull init
      $c->init();
    }

    parent::init();
  }

}

