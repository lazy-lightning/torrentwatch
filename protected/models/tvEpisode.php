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
      array('title','length','max'=>128),
      array('season, episode, lastUpdated, status', 'numerical', 'integerOnly'=>true),
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

  public function beforeValidate($type) {
    $this->lastUpdated = time();
    if($this->isNewRecord) {
      $this->status = self::STATUS_NEW;
    }

    return parent::beforeValidate($type);
  }

  public function getEpisodeString($empty = TRUE) {
    if($this->season == 0 && $this->episode == 0)
      return $empty ? '' : 'Single Episode';
    elseif($this->episode > 10000)
      return date('Y-m-d', $this->episode);
    else 
      return sprintf('S%02dE%02d', $this->season, $this->episode);
  }
}
