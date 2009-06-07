<?php

abstract class ARwithQuality extends CActiveRecord {
  private $_qualityIds;

  public function getQualityIds() {
    if($this->_qualityIds === null) {
      $relations = feedItem_quality::model()->findAllByAttributes(array('quality_id' => $this->id));

      $ids = array();
      foreach($relations as $record) {
        $ids[] = $record->quality_id;
      }

      $this->_qualityIds = $ids;
    }
    return $this->_qualityIds;
  }

  public function setQualityIds($value) {
    $tmp = array();
    Yii::log(print_r($value, TRUE), CLogger::LEVEL_ERROR);
    foreach($value as $val) {
      if($val != -1)
        $tmp[] = $val;
    }
    Yii::log(print_r($tmp, TRUE), CLogger::LEVEL_ERROR);
    if(count($tmp) !== 0)
      $this->_qualityIds = $value;
  }

  public function afterSave() {
    // update scenario
    // Clean out any quality relations if this isn't new
    $table = $this->tableName();
    $class = $table.'_quality';
    $id = $table.'_id';
    if(!$this->isNewRecord) {
      $model = new $class;
      $model->deleteAll($id.'=:id', array(':id'=>$this->id));
    }

    // set quality relations
    foreach($this->qualityIds as $qualityId) {
      $relation = new $class;
      $relation->$id = $this->id;
      $relation->quality_id = $qualityId;
      $relation->save();
    }

    parent::afterSave();
  }
}

