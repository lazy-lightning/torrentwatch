<?php

abstract class BaseFavorite extends ARwithQuality
{

  public function rules()
  {
    return array(
        array('queue', 'default', 'value'=>1),
        array('feed_id', 'default', 'value'=>0),
        array('saveIn', 'writableDirectory'),
        array('feed_id', 'exist', 'allowEmpty'=>False, 'attributeName'=>'id', 'className'=>'feed'),
        array('queue', 'in', 'allowEmpty'=>False, 'range'=>array(0,1)),
    );
  }

  /**
   * Check for any newly matching favorites
   */
  public function afterSave()
  {
    parent::afterSave();
    Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NOMATCH);
  }

  /**
   * Reset any currently matching feed Items that arn't downloaded to nomatch
   */
  public function beforeSave()
  {
    if($this->isNewRecord === False) {
      Yii::app()->dlManager->resetMatching($this);
    }
    return parent::beforeSave();
  }

  /**
   * Clean out many_many relationship tables on delete
   */
  public function deleteByPk($pk,$condition='',$params=array())
  {
    if(parent::deleteByPk($pk, $condition, $params))
    {
      $table = $this->tableName();
      $class = $table.'_quality';
      $model = new $class;
      $model->deleteAll($table.'_id = :id', array(':id'=>$pk));
      return True;
    }
    return False;
  }

  /**
   * Validation function
   */
  public function writableDirectory($attribute, $params) {
    if(!empty($this->$attribute) &&
       False == (is_dir($this->$attribute) && is_writable($this->$attribute)))
      $this->addError($attribute, $this->$attribute." is not a writable directory");
  }
}

