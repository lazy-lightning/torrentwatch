<?php

class feedItem extends ARwithQuality
{

  // Higher numbers so they can be sorted as "better" matches
  const STATUS_NEW = 0;
  const STATUS_NOMATCH = 1;
  const STATUS_MATCH = 2;
  const STATUS_DUPLICATE = 6;
  const STATUS_OLD = 7;
  const STATUS_QUEUED = 15;
  const STATUS_FAILED_DL = 19;
  const STATUS_AUTO_DL = 20;
  const STATUS_MANUAL_DL = 21;

  // Download Types
  const TYPE_TORRENT = 0;
  const TYPE_NZB = 1;

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
    return 'feedItem';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      array('lastUpdated', 'default', 'setOnEmpty'=>false, 'value'=>time()),
      array('hash', 'length', 'allowEmpty'=>false, 'is'=>32),
      array('feed_id', 'default', 'value'=>0),
      array('feed_id', 'exist', 'allowEmpty'=>false, 'attributeName'=>'id', 'className'=>'feed'),
      array('movie_id', 'exist', 'attributeName'=>'id', 'className'=>'movie'),
      array('other_id', 'exist',  'attributeName'=>'id', 'className'=>'other'),
      array('tvEpisode_id', 'exist', 'attributeName'=>'id', 'className'=>'tvEpisode'),
      array('pubDate', 'default', 'value'=>time()),
      array('pubDate', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=>0),
      array('status', 'default', 'value'=>self::STATUS_NEW),
      array('status', 'in', 'allowEmpty'=>false, 'range'=>array_keys($this->getStatusOptions())),
      array('downloadType', 'in', 'allowEmpty'=>false, 'range'=>array_keys(feedItem::getStatusOptions())),
    );
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
        'feed'=>array(self::BELONGS_TO, 'feed', 'feed_id'),
        'quality'=>array(self::MANY_MANY, 'quality', 'feedItem_quality(feedItem_id, quality_id)'),
        // Belongs to only one of the next 3
        'tvEpisode'=>array(self::BELONGS_TO, 'tvEpisode', 'tvEpisode_id'),
        'movie'=>array(self::BELONGS_TO, 'movie', 'movie_id'),
        'other'=>array(self::BELONGS_TO, 'other', 'other_id'),
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
   * all valid download types and their string mappings
   * @return array number=>string pairs 
   */
  public static function getDownloadTypeOptions() {
    return array(
        self::TYPE_TORRENT=>'Torrent',
        self::TYPE_NZB=>'NZB',
    );
  }

  /**
   * @return string String representation of download type
   */
  public static function getDownloadTypeText($downloadType=null) {
    if($downloadType===null)
      $downloadType = $this->downloadType;

    $options=$this->getDownloadTypeOptions();
    return isset($options[$downloadType]) ? $options[$downloadType]
        : "unknown ($downloadType)";
  }

  /**
   * all valid statuses and their string mappings
   * @return array number=>string pairs
   */
  public static function getStatusOptions() {
    return array(
        self::STATUS_AUTO_DL=>'Automatic Download',
        self::STATUS_DUPLICATE=>'Duplicate Episode',
        self::STATUS_FAILED_DL=>'Failed Download',
        self::STATUS_NEW=>'New',
        self::STATUS_NOMATCH=>'Unmatched',
        self::STATUS_MANUAL_DL=>'Manual Download',
        self::STATUS_MATCH=>'Matched',
        self::STATUS_OLD=>'Old Episode',
        self::STATUS_QUEUED=>'Queued for User',
    );
  }

  // static to allow translation directly from query row in a view without AR model
  public static function getStatusText($status = null) {
    if($status === null)
      $status = $this->status;
    $options=self::getStatusOptions();
    return isset($options[$status]) ? $options[$status]
        : "unknown ({$status})";
  }
      
  public function beforeValidate($type) {
    if($this->isNewRecord) {
      list($shortTitle, $season, $episode, $network, $quality) = mediaTitleParser::detect($this->title);
  
      if(!empty($network))
        $this->network_id = factory::networkByTitle($network)->id;

      $this->qualityIds = factory::qualityIdsByTitleArray($quality);
     
      if(($season >= 0 && $episode > 0) ||
         ($season > 0 && $episode == 0)) 
      {
        // Found a season and episode for this item
        $this->tvEpisode_id = factory::tvEpisodeByEpisode($shortTitle, $season, $episode)->id;
      }
      elseif($this->imdbId > 1000) 
      {
        // IMdB id is not best differentiator, but will work for now
        $this->movie_id = factory::movieByImdbId($this->imdbId, $shortTitle)->id;
      }
      else 
      {
        $this->other_id = factory::otherByTitle($shortTitle)->id;
      }
    }
    return parent::beforeValidate($type);
  }

  /**
   * Create a favorite that would match this feed item
   * @return object an unsaved favorite
   */
  public function generateFavorite() {
    $fav = null;
    $itemType = $this->itemTypeRecord;
    if($itemType === null || get_class($itemType) === 'other')
    {
      $fav = new favoriteString;
      $fav->filter = $fav->name = $this->title;
    }
    else
    {
      $fav = $itemType->generateFavorite($this);
    }

    if($fav) {
      $ids = array();
      foreach($this->quality as $quality)
        $ids[] = $quality->id;
      $fav->qualityIds = $ids;
    }

    return $fav;
  }

  public function getItemTypeRecord()
  {
    if(!empty($this->tvEpisode_id))
      return $this->tvEpisode;
    elseif(!empty($this->movie_id))
      return $this->movie;
    elseif(!empty($this->other_id))
      return $this->other;
    return null;
  }
}
