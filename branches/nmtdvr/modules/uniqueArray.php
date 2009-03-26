<?php
class uniqueArray {
  private $array = array();
  private $requiredClassType;
  private $maxItems = 0;

  public function __construct($requiredClassType = False) {
    $this->requiredClassType = $requiredClassType;
  }

  public function add($newArrayItem) {
    if(!$this->isValidArrayItem($newArrayItem)) {
      SimpleMvc::log(get_class($this).' rejecting item: '.get_class($newArrayItem).' is not Valid');
      return False;
    }

    $this->array[] = $newArrayItem;
    $idx = end(array_keys($this->array));
    $newArrayItem->setId($idx);
    $this->enforceMaxItems();
    SimpleMvc::log(get_class($this).': successfull add ID '.$idx);
    return $idx;
  }

  public function del($idx) {
    if(isset($this->array[$idx])) {
      unset($this->array[$idx]);
      return True;
    }
    return False;
  }

  public function emptyArray() {
    unset($this->array);
    $this->array = array();
  }

  protected function enforceMaxItems() {
    if($this->maxItems > 0) {
      $c = count($this->array);
      if($c > $this->maxItems) {
        SimpleMvc::log(__FUNCTION__.": items: $c maxItems: ".$this->maxItems);
        // the [] operator adds to the end, so the oldest items
        // are at the begining of the array
        $this->array = array_slice($this->array, $c - $this->maxItems, null, True);
      }
    }
  }

  public function get($id = '', $key = '', $onlyFirst = True) {
    if(empty($this->array))
      return False;

    // no options: return full array
    if($id === '') {
      return $this->array;
    }
    // one option, return by array key
    if($key == '') {
      if(isset($this->array[$id]))
        return $this->array[$id];
      else
        return False;
    }
    // find objs by property will check standard or in_array
    // return array of items by passing False to onlyFirst
    // must have a requiredClass to guarantee all objects are the same
    if($this->requiredClassType === False) {
      SimpleMvc::log(get_class($this)." attempted get() without requiredClassType");
      return False;
    }

    $output = array();
    foreach($this->array as $idx => $obj) {
      if(is_array($obj->$key)) {
        if(in_array($id, $obj->$key)) {
          if($onlyFirst) { return $obj; } else { $output[$idx] = $obj; }
        }
      }  else if($obj->$key == $id) {
          if($onlyFirst) { return $obj; } else { $output[$idx] = $obj; }
      }
    }

    return $onlyFirst ? False : $output;
  }

  // Returns the reverse of above with 2 or 3 options
  public function getNot($id, $key, $onlyFirst = True) {
    return array_diff_assoc($this->array, $this->get($id, $key, $onlyFirst));
  }

  public function isEmpty() {
    return empty($this->array);
  }

  public function isValidArrayItem($obj) {
    if($obj instanceof  arrayItem &&
       ($this->requiredClassType === False || $obj instanceof $this->requiredClassType)) {
      return True;
    }
    return False;
  }

  protected function load(&$data) {
    $this->array =& $data;
  }

  public function setMaxItems($count) {
    $this->maxItems = $count;
    $this->enforceMaxItems();
  }
}

