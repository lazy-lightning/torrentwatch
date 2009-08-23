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
      array('title','required'),
      array('network_id', 'exist', 'attributeName'=>'id', 'className'=>'network'),
      array('rating', 'numerical', 'integerOnly'=>true, 'min'=>0, 'max'=>10),
      array('tvdbId', 'numerical', 'integerOnly'=>true, 'min'=>0),
      array('lastTvdbUpdate', 'default', 'value'=>0),
      array('lastTvdbUpdate', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=>0),
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

  /**
   * @return string description with max 256 characters
   */
  public function getShortDescription($length = 256, $append = ' ...')
  {
    if(strlen($this->description) <= $length)
      $desc = $this->description;
    else
      $desc = substr($this->description, 0, 256-strlen($append)).$append;
    return $desc;
  }
}
