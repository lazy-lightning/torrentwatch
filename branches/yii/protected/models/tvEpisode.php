<?php

class tvEpisode extends CActiveRecord
{

  const STATUS_NEW = 0;
  const STATUS_DOWNLOADED = 1;

  public function getStatusOptions() {
    return array(
        self::STATUS_NEW=>'New',
        self::STATUS_DOWNLOADED=>'Downloaded',
    );
  }

  public function getStatusText() {
    $options = $this->getStatusOptions();
    return isset($options[$this->status]) ? $options[$this->status]
        : "unknown ({$this->status})";
  }

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
    return 'tvEpisode';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      array('lastUpdated', 'default', 'setOnEmpty'=>false, 'value'=>time()),
      array('tvShow_id', 'exist', 'allowEmpty'=>false, 'attributeName'=>'id', 'className'=>'tvShow'),
      array('lastTvdbUpdate', 'default', 'value'=>0),
      array('lastTvdbUpdate, season, episode', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=>0),
      array('status', 'default', 'value'=>self::STATUS_NEW),
      array('status', 'in', 'allowEmpty'=>false, 'range'=>array_keys($this->getStatusOptions())),
    );
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
        'feedItems'=>array(self::HAS_MANY, 'feedItem', 'tvEpisode_id'),
        'tvShow'=>array(self::BELONGS_TO, 'tvShow', 'tvShow_id'),
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
   * set firstAired if episode is an air date
   * @return if validation should continue
   */
  public function beforeValidate()
  {
    if($this->episode > 10000 && empty($this->firstAired))
      $this->firstAired = $this->episode;
    return parent::beforeValidate();
  }

  /**
   * @return favoriteMovie a favoriteMovie object to match this movie
   */
  public function generateFavorite($feedItem) {
    $fav = new favoriteTvShow;
    $fav->tvShow_id = $this->tvShow_id;
    $fav->onlyNewer = 1;
    return $fav;
  }

  /**
   * @return string a string representation of this records episode
   */
  public function getEpisodeString($empty = TRUE) {
    if($this->episode > 10000)
      return date('Y-m-d', $this->episode);
    elseif($this->season > 0 && $this->episode == 0)
      return sprintf('S%02dE??', $this->season);
    else 
      return sprintf('S%02dE%02d', $this->season, $this->episode);
  }

}
