<?php

class history extends CActiveRecord
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
    return 'history';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      array('date', 'default', 'value'=>time()),
      array('feedItem_id, feed_id, status, date', 'numerical', 'integerOnly'=>true),
    );
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
    );
  }

  /**
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels()
  {
    return array(
      'id'=>'Id',
      'feedItem_id'=>'Feed Item ',
      'feedItem_title'=>'Feed Item Title',
      'feed_id'=>'Feed ',
      'feed_title'=>'Feed Title',
      'favorite_name'=>'Favorite Name',
      'status'=>'Status',
      'date'=>'Date',
      'favorite_type'=>'Favorite Type',
    );
  }

}
