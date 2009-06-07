<?php

class tvShow extends CActiveRecord
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
		return 'tvShow';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('title','length','max'=>128),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
      'favorites'=>array(self::HAS_MANY, 'favoriteTvShow', 'tvShow_id'),
      'genres'=>array(self::MANY_MANY, 'genre', 'tvShow_genre(tvShow_id, genre_id)'),
      'network'=>array(self::BELONGS_TO, 'network', 'network_id'),
      'tvEpisodes'=>array(self::HAS_MANY, 'tvEpisode', 'tvShow_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
		);
	}
}