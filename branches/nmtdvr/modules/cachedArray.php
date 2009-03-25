<?php
class cachedArray extends uniqueArray {
  private $label;
  private $uniqueId;
  public $changed = False;

  public function __construct($requiredClass, $uniqueId = '') {
    parent::__construct($requiredClass);
    $this->uniqueId = empty($uniqueId) ? $requiredClass : $uniqueId;
    $this->label = $requiredClass;
    $this->load();
  }

  public function __destruct() {
    SimpleMvc::log("Considering save of ".get_class($this).": {$this->label} - {$this->uniqueId}");
    if($this->changed)
      return $this->save();
    // Split because you cant guarantee the order php runs the compares
    // in an if statement, and above is always faster
    if($this->get(TRUE, 'changed'))
      return $this->save();
  }

  public function add($newItem) {
    if(False !== ($idx = parent::add($newItem))) {
      $this->changed = True;
      return $idx;
    }
    return False;
  }

  public function del($uniqueId) {
    if(parent::del($uniqueId)) {
      $this->changed = True;
      return True;
    }
    return False;
  }

  public function emptyArray() {
    $this->changed = True;
    parent::emptyArray();
  }

  public function isValidArrayItem($obj) {
    if($obj instanceof cacheItem)
      return parent::isValidArrayItem($obj);
    return False;
  }

  protected function load() {
    $data = DataCache::Get($this->label, $this->uniqueId);
    if($data)
      parent::load($data);
  }

  private function save() {
    SimpleMvc::log(get_class($this)." performing save: {$this->label} - {$this->uniqueId}");
    DataCache::Put($this->label, $this->uniqueId, 31270000, $this->get());
  }

}

