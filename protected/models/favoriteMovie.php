<?php

class favoriteMovie extends ARwithQuality
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
		return 'favoriteMovies';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('name', 'required'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
        'feed' => array(self::BELONGS_TO, 'feed', 'feed_id'),
        'quality' => array(self::MANY_MANY, 'quality', 'favoriteMovies_quality(favoriteMovies_id, quality_id)'),
        'genre' => array(self::BELONGS_TO, 'genre', 'genre_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'=>'Id',
			'name'=>'Name',
			'genre_id'=>'Genre ',
			'quality_id'=>'Quality ',
			'rating'=>'Rating',
		);
	}

  public function afterSave()
  {
    parent::afterSave();
    Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NOMATCH);
  }
}
