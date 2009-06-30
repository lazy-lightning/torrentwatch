<?php

class favoriteString extends BaseFavorite
{
  /**
   * Returns the static model of the specified AR class.
   * @return CActiveRecord the static model class
   */
  public static function model($className=__CLASS__)
  {
    return parent::model($className);
  }

  /**
   * @return string the associated database table name
   */
  public function tableName()
  {
    return 'favoriteStrings';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array_merge(parent::rules(), array(
          array('name, filter', 'required'),
          array('filter, notFilter', 'convertGlobtoSQL')
    ));
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
        'feed' => array(self::BELONGS_TO, 'feed', 'feed_id'),
        'quality' => array(self::MANY_MANY, 'quality', 'favoriteStrings_quality(favoriteStrings_id, quality_id)'),
    );
  }

  /**
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels()
  {
    return array(
      'id'=>'Id',
      'filter'=>'Filter',
      'notFilter'=>'Not Filter',
      'saveIn'=>'Save In',
      'feed_id'=>'Feed',
      'queue'=>'Queue matches',
    );
  }

  public function afterSave()
  {
    parent::afterSave();
    Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NOMATCH);
  }

  public function convertGlobToSQL($attribute, $params)
  {
    $value = $this->$attribute;
    $this->$attribute = empty($value) ? NULL : strtr($value, '*', '%');
    return true;
  }
}
