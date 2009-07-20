<?php

class favoriteTvShow extends BaseFavorite
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
    return 'favoriteTvShows';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array_merge(parent::rules(), array(
          array('onlyNewer', 'default', 'value'=>0),
          array('onlyNewer', 'in', 'allowEmpty'=>False, 'range'=>array(0, 1)),
          array('tvShow_id', 'exist', 'allowEmpty'=>False, 'attributeName'=>'id', 'className'=>'tvShow'),
          array('minEpisode, maxEpisode, minSeason, maxSeason', 'default', 'value'=>0),
          array('minEpisode', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=> 0, 'max'=> $this->maxEpisode),
          array('maxEpisode', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=> $this->minEpisode),
          array('minSeason', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=> 0, 'max'=> $this->maxSeason),
          array('maxSeason', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=> $this->minSeason),
    ));
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
        'feed' => array(self::BELONGS_TO, 'feed', 'feed_id'),
        'tvShow' => array(self::BELONGS_TO, 'tvShow', 'tvShow_id'),
        'quality' => array(self::MANY_MANY, 'quality', 'favoriteTvShows_quality(favoriteTvShows_id, quality_id)'),
    );
  }

  /**
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels()
  {
    return array(
        'tvShow_id'=>'TV Show',
        'feed_id'=>'Feed',
        'onlyNewer'=>'Only download newer episodes',
    );
  }

  /**
   * validation routine converts tvShow_id from string into an id on
   * a new record if neccessary
   * @return boolean continue validation process
   * TODO: convert into generic validation function or class
   */
  public function beforeValidate($type)
  {
    if($this->isNewRecord && !is_numeric($this->tvShow_id)) {
      if(empty($this->tvShow_id))
      {
        $this->addError('tvShow_id', 'Please specify a TV Show');
      } 
      else try 
      {
        $this->tvShow_id = factory::tvShowByTitle($this->tvShow_id)->id;
      } catch ( Exception $e) {
        $this->addError("tvShow_id", "There was a problem initilizing a tvshow of that title");
      }
    }

    return parent::beforeValidate($type);
  }

  /**
   * @return string title of the tvShow favorited
   */
  public function getName()
  {
    return $this->tvShow->title;
  }
}