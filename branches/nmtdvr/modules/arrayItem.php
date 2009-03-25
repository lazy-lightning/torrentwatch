<?php
abstract class arrayItem {
  private $id;

  public function __construct($options) {
    $this->update($options);
  }

  public function __get($name) {
    echo '<pre>__get('.$name.'): '.print_r($name, TRUE).'</pre>';
    $func = 'get'.ucfirst($name);
    if(method_exists($this, $func)) {
      return $this->$func();
    }elseif(property_exists($this, $name)) {
      return $this->$name;
    }
    throw new Exception();
  }

  public function __set($name, $value) {
    $this->update(array($name => $value));
  }

  public function __sleep() {
    return array("\x00arrayItem\x00id");
  }

  public function getId() { return $this->id; }

  public function setId($id) {
    if(!isset($this->id)) {
      $this->id = $id;
      return True;
    }
    return False;
  }

  public function update($options) {
    foreach($options as $key => $value) {
      $func = 'set'.ucfirst($key);
      if(method_exists($this, $func)) {
        $this->$func($value);
      } elseif(property_exists($this, $key)) {
        $this->$key = $value;
      } else SimpleMvc::log(get_class($this).': Property does not exist(or is private): '.$key);
    }
    SimpleMvc::log(get_class($this)." ".$this->id." updated");
  }

}

