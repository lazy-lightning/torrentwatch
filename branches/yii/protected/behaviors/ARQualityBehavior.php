<?php

// many of the ar classes have a many_many relationship
// with the quality table.  This standardizes their setting
// and getting

class ARQualityBehavior extends CActiveRecordBehavior 
{
  private $_qualityIds;

  public function getQualityIds() 
  {
    if($this->_qualityIds === null) 
    {
      $table = $this->Owner->tableName();
      $class = $table.'_quality';
      $id = $table.'_id';
      $relations = CActiveRecord::model($class)->findAllByAttributes(
          array($id => $this->Owner->id)
      );

      $this->_qualityIds = array();
      foreach($relations as $record) 
      {
        $this->_qualityIds[] = $record->quality_id;
      }
    }
    return $this->_qualityIds;
  }

  public function getQualityString() 
  {
    $string = array();
    foreach($this->Owner->quality as $quality) 
    {
      $string[] = $quality->title;
    }
    return implode(' / ', $string);
  }

  public function setQualityIds($in) 
  {
    Yii::log(__FUNCTION__);
    $this->_qualityIds = array();
    foreach($in as $val) 
    {
      if($val >= 0)
        $this->_qualityIds[] = $val;
    }
  }

  public function afterSave($event) 
  {
    Yii::log(print_r($this->getQualityIds(),true));
    // update scenario
    // Clean out any quality relations if this isn't new
    $table = $this->Owner->tableName();
    $class = $table.'_quality';
    $id = $table.'_id';
    if(!$this->Owner->isNewRecord) 
    {
      CActiveRecord::model($class)->deleteAll($id.'=:id', array(':id'=>$this->Owner->id));
    }

    // set quality relations
    foreach($this->getQualityIds() as $qualityId) 
    {
      $relation = new $class;
      $relation->$id = $this->Owner->id;
      $relation->quality_id = $qualityId;
      $relation->save();
    }
  }
}

