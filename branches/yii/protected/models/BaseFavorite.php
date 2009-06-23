<?php

abstract class BaseFavorite extends ARwithQuality
{

  public function rules()
  {
    return array(
        array('saveIn', 'writableDirectory'),
        array('feed_id', 'validFeed'),
        array('queue', 'in', 'range'=>array(0,1)),
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
      $table = $this->tableName();
      $this->dbConnection->createCommand(
          'UPDATE feedItem SET status='.feedItem::STATUS_NOMATCH.
          ' WHERE feedItem.id IN ( SELECT feedItem_id as id FROM matching'.$table.' m'.
                                  ' WHERE m.'.$table.'_id = '.$this->id.
                                  '   AND m.feedItem_status NOT IN ("'.
                                    feedItem::STATUS_AUTO_DL.'", "'.feedItem::STATUS_MANUAL_DL.'"));'
      )->execute();
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
  public function validFeed($attribute, $params) {
    if(False === feed::model()->exists('id = :id', array(':id'=>$this->$attribute)))
      $this->addError($attribute, 'Must be a valid Feed Id');
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

