<?php
class chain {
  private $chain = array();

  public function add($obj) {
    $this->chain[] = $obj;
  }

  public function run($obj, $args = null) {
    foreach($this->chain as $item) {
      if($item->run($obj, $args) === False) {
        SimpleMvc::log('rejected by '.get_class($item));
        return False;
      }
    }
    return True;
  }
}
