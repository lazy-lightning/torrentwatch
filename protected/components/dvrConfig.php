<?php

class dvrConfig extends CAttributeCollection {
  private $_changed;

  public function tableName() {
    return 'dvrConfig';
  }

  public function init() {
    $table = $this->tableName();
    $reader = Yii::app()->db->createCommand('select key,value from '.$this->tableName())->query();
    $data = array();
    foreach($reader as $row) {
      $data[$row['key']] = $row['value'];
    }
    $this->copyFrom($data);
    $this->_changed = array();
  }

  public function save() {
    $key = $value = 0;
    $cmd = Yii::app()->db->createCommand('update '.$this->tableName().' SET value=:value WHERE key=:key');
    $cmd->bindParam(':key', $key);
    $cmd->bindParam(':value', $value);
    foreach($this->_changed as $key => $foo) {
      $value = $this->$key;
      $cmd->execute();
    }
    $this->_changed = array();
  }

  public function add($key, $value) {
    parent::add($key, $value);
    $this->_changed[$key] = TRUE;
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

