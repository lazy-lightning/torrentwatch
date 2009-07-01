<?php

abstract class BaseDvrConfig extends CModel {
  private $_updateCommand; // CDbCommand that can update a value from this category
  private $_changed=array(); // changed items since load
  private $_ar = array(); // loaded values
  private $allowNewEntries = True; // If new items can be added to the array

  protected $_id = null; // id from the dvrConfigCategory table

  /**
   * override __get to retreive variables from our internal config array
   * @param string the requested variables name
   * @return mixed the requested variables value
   */
  public function __get($name) {
    if($this->contains($name))
      return $this->_ar[$name];

    return parent::__get($name);
  }

  /**
   * override __set to set variables from our internal config array
   * @param string $name the attribute name to set
   * @param mixed $value the value to be associated with the name
   */
  public function __set($name, $value) {
    if($this->contains($name))
      $this->add($name, $value);
    else
      parent::__set($name, $value);
  }

  /**
   * add an attribute to the internal config array
   * @param string $key the key to add to the config array
   * @param string $value the value assign to the key
   */
  public function add($key, $value) {
    if($this->allowNewEntries || $this->contains($key))
    {
      $this->_ar[$key] = $value;
      $this->_changed[$key] = true;
    }
  }

  /**
   * reset the array of changed attributes after saving
   */
  public function afterSave()
  {
    $this->_changed = array();
  }

  /**
   * Returns the list of all attributeNames of the category.
   * @return array list of attribute names
   */
  public function attributeNames() 
  {
    return array_keys($this->_ar);
  }

  /**
   * @return boolean if the save should proceede
   */
  public function beforeSave()
  {
    return True;
  }

  /**
   * @param string $key a key to check for
   * @return boolean if the given key exists
   */
  public function contains($key)
  {
    return isset($this->_ar[$key]);
  }

  /**
   * Returns a CDbCommand capable of updating a row for this category
   * @return CDbCommand command requiring :key and :value to be bound
   */
  private function getUpdateCommand()
  {
    if(!$this->_updateCommand)
    {
      $this->_updateCommand = Yii::app()->db->createCommand(
          'UPDATE dvrConfig SET value = :value WHERE key = :key AND dvrConfigCategory_id '
          .($this->_id === null ? 'IS NULL' : '= :catId')
      );
      if($this->_id !== null)
        $this->_updateCommand->bindValue(':catId', $this->_id);
    }
    return $this->_updateCommand;
  }

  /**
   * dissalow new entries to the category after initialization
   * and reset the changed attributes array
   */
  public function init()
  {
    $this->allowNewEntries = false;
    $this->_changed = array();
  }

  /**
   * unused with override of the setAttributes function
   */
  public function safeAttributes()
  {
  }

  /**
   * save any changed values to the database
   * @param boolean weather to perform validation before saving the record
   * If the validation fails, the record will not be saved to the database
   * the validation will be performed under the 'update' scenario
   * @return boolean weather saving succeeds
   */
  public function save($runValidation=true) {
    if(!$runValidation || $this->validate('update'))
      return $this->update(array_keys($this->_changed));
    else
      return false;
  }

  /**
   * Override setAttributes from CModel to allow mass assignment
   * for all attributes contained in the category
   */
  public function setAttributes($values, $scenario='') {
    Yii::log(__FUNCTION__.': '.print_r($values, true), CLogger::LEVEL_ERROR);
    foreach($values as $name=>$value)
    {
      $this->add($name, $value);
    }
  }

  /**
   * Tag an attribute as changed since load
   * @param string the key to tag
   */
  public function setChanged($key) {
    $this->_changed[$key] = true;
  }

  /**
   * Updates the rows represented by this category
   * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
   * @param array list of attributes to be saved.  Defaults to null,
   * meaning all attributes that were loaded from DB will be saved.
   * @return boolean whether the update is successful
   */
  public function update($attributes=null)
  {
    if($this->beforeSave())
    {
      Yii::log('saving '.print_r($this, TRUE), CLogger::LEVEL_ERROR);

      if($attributes===null)
        $attributes = $this->attributeNames();
      foreach($attributes as $key) 
      {
        $value = $this->$key;
        if(is_object($value))
          $value->save();
        else 
          $this->updateByKey($key, $value);
      }

      $this->afterSave();
      return true;
    }

    return false;
  }

  /**
   * Updates a single row represented by the key and this objects category
   * @param string $key the key to be updated
   * @param mixed $value the value to be associated with the key
   */
  private function updateByKey($key, $value)
  {
    $cmd = $this->getUpdateCommand();
    $cmd->bindValue(':key', $key);
    $cmd->bindValue(':value', $value);
    $cmd->execute();
  }

}

class dvrConfigCategory extends BaseDvrConfig {
  private $_parent;
  private $_title;

  /**
   * Constructor.
   * @param dvrConfig $parent the object instantiating this class
   * @param string the title of this category
   * @param array an array of key=>value pairs to assign to the category
   */
  public function __construct($parent, $id, $title, $values) {
    $this->_parent = $parent;
    $this->_title = $title;
    $this->_id = $id;
    foreach($values as $row) {
      $this->add($row['key'], $row['value']);
    }
  }

  /**
   * Notifies parent of any add events to propogate save
   * @param string the key to be added
   * @param mixed the value to be associated with said key
   */
  public function add($key, $value) {
    parent::add($key, $value);
    $this->_parent->setChanged($this->_title);
  }

  /**
   * Initalize this subcategory
   */
  public function init() {
    $this->_parent->add($this->_title, $this);
    parent::init();
  }

  /**
   * Returns the title of this sub-category
   * @return string
   */
  public function getTitle() {
    return $this->_title;
  }

}

class dvrConfig extends BaseDvrConfig {

  /**
   *
   */
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
      $c = new dvrConfigCategory($this, $id, $row['title'], empty($data[$id]) ? array() : $data[$id]);
      // dvrConfigCategory will add to parent on successfull init
      $c->init();
    }

    parent::init();
  }

}

