<?php

class favoriteString extends ARwithQuality
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
		return array(
			array('feed_id', 'numerical', 'integerOnly'=>true),
		);
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
			'seedRatio'=>'Seed Ratio',
			'feed_id'=>'Feed ',
			'foo'=>'Foo',
		);
	}

  public function afterSave()
  {
    parent::afterSave();
    Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NOMATCH);
  }
}
