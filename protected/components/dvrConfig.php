<?php

class dvrConfig extends BaseDvrConfig {

  private $_state = True;

  protected $_key = 'id';
  protected $_value = 'title';

  public function tableName() {
    return $this->_state ?  'dvrConfigCategory' : 'dvrConfig';
  }

  public function init() {
    // pull in the various categorys and initialize them
    parent::init();
    foreach($this as $id => $title) {
      Yii::log("initializing category $id: $title", CLogger::LEVEL_ERROR);
      $this->add($title, new dvrConfigCategory($this, $id, $title));
    }
    // Flip state to dvrConfig table and contain any values with null category id
    $this->_state = false;
    $this->_key = 'key';
    $this->_value = 'value';
    parent::init('dvrConfigCategory_id IS NULL');
  }

}

class dvrConfigCategory extends BaseDvrConfig {
  private $_id;
  private $_title;
  private $_parent;

  protected $_key = 'key';
  protected $_value = 'value';

  public function __construct($parent, $id, $title) {
    $this->_title = $title;
    $this->_id = $id;
    $this->_parent = $parent;
    parent::__construct();
    $this->init();
  }

  public function tableName() {
    return 'dvrConfig';
  }

  public function add($key, $value) {
    parent::add($key, $value);
    $this->_parent->setChanged($this->_title);
  }

  public function init() {
    parent::init('dvrConfigCategory_id = '.$this->_id);
  }

  public function getTitle() {
    return $this->_title;
  }
}

abstract class BaseDvrConfig extends CAttributeCollection {
  protected $_changed, $_key, $_value;

  abstract public function tableName();

  public function init($where = null) {
    if($this->_key === null OR $this->_value === null)
      throw new CException(__CLASS__." initialized without proper key/value pair");

    $sql = "SELECT {$this->_key},{$this->_value} FROM ".$this->tableName();
    if($where !== null)
      $sql .= ' WHERE '.$where;
    yii::log($sql, CLogger::LEVEL_ERROR);

    $reader = Yii::app()->db->createCommand($sql)->query();
    $data = array();
    foreach($reader as $row) {
      $this->add($row[$this->_key], $row[$this->_value]);
    }
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
    $cmd = Yii::app()->db->createCommand('UPDATE '.$this->tableName()." SET {$this->_value} = :value WHERE {$this->_key} = :key");
    $cmd->bindParam(':key', $key);
    $cmd->bindParam(':value', $value);
    foreach($this->_changed as $key => $foo) {
      $value = $this->$key;
      if(is_object($value))
        $value->save();
      else
        $cmd->execute();
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

