<?php

// many of the ar classes have a many_many relationship
// with the quality table.  This standardizes their setting
// and getting

abstract class ARwithQuality extends CActiveRecord {
  private $_qualityIds;

  public function getQualityIds() {
    if($this->_qualityIds === null) {
      // cant use the class::model()-> syntax with a dynamic
      // class name
      $class = $this->tableName().'_quality';
      $model = new $class;
      $relations = $model->findAllByAttributes(array('quality_id' => $this->id));

      $ids = array();
      foreach($relations as $record) {
        $ids[] = $record->quality_id;
      }

      $this->_qualityIds = $ids;
    }
    return $this->_qualityIds;
  }

  public function setQualityIds($in) {
    $out = array();
    Yii::log("quality ids in: ".print_r($in, true), CLogger::LEVEL_ERROR);
    foreach($in as $val) {
      if($val >= 0)
        $out[] = $val;
    }
    Yii::log("quality ids out: ".print_r($out, true), CLogger::LEVEL_ERROR);
    $this->_qualityIds = $out;
  }

  public function afterSave() {
    // update scenario
    // Clean out any quality relations if this isn't new
    // cant use class::model() syntax with dynamic class name
    $table = $this->tableName();
    $class = $table.'_quality';
    $id = $table.'_id';
    if(!$this->isNewRecord) {
      $model = new $class;
      $model->deleteAll($id.'=:id', array(':id'=>$this->id));
    }

    // set quality relations
    foreach($this->qualityIds as $qualityId) {
      Yii::log("load $class and set $id = {$this->id} and quality_id = $qualityId", CLogger::LEVEL_ERROR);
      $relation = new $class;
      $relation->$id = $this->id;
      $relation->quality_id = $qualityId;
      $relation->save();
    }

    parent::afterSave();
  }
}

