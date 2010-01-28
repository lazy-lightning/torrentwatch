<?php

class ARStatusTextBehavior extends CActiveRecordBehavior
{
  public function getStatusOptions() {
    $class = get_class($this->getOwner());
    return array(
        constant("$class::STATUS_NEW")=>'Unmatched',
        constant("$class::STATUS_DOWNLOADED")=>'Downloaded',
    );
  }

  public function getStatusText() {
    $options = $this->getStatusOptions();
    $status = $this->Owner->status;
    return isset($options[$status]) ? $options[$status]
        : "unknown ({$status})";
  }

}

