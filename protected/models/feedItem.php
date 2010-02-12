<?php

/**
 * feedItem Class File
 * 
 * @uses CActiveRecord
 * @version $id$
 * @copyright 1997-2005 The PHP Group
 * @author Tobias Schlitt <toby@php.net> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class feedItem extends CActiveRecord
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
   * @return array behaviors to be attached
   */
  public function behaviors()
  {
    return array(
        'quality' => array('class'=>'ARQualityBehavior')
    );
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      array('lastUpdated', 'default', 'setOnEmpty'=>false, 'value'=>time()),
      array('hash', 'length', 'allowEmpty'=>false, 'is'=>32),
      array('feed_id', 'exist', 'allowEmpty'=>false, 'attributeName'=>'id', 'className'=>'feed'),
      array('movie_id', 'exist', 'attributeName'=>'id', 'className'=>'movie'),
      array('other_id', 'exist',  'attributeName'=>'id', 'className'=>'other'),
      array('tvEpisode_id', 'exist', 'attributeName'=>'id', 'className'=>'tvEpisode'),
      array('pubDate', 'default', 'value'=>time()),
      array('pubDate', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=>0),
      array('status', 'default', 'value'=>self::STATUS_NEW),
      array('status', 'in', 'allowEmpty'=>false, 'range'=>array_keys($this->getStatusOptions())),
      array('downloadType', 'in', 'allowEmpty'=>false, 'range'=>array_keys(feedItem::getStatusOptions())),
      array('title,description', 'notMultibyte'),
      array('url', 'url', 'allowEmpty'=>false),
      array('imdbId', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=>0),
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
   * getDownloadTypeText 
   * 
   * @param mixed $downloadType 
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

  /**
   * getStatusText 
   * 
   * @param integer $status 
   * @return string the given status as a string
   */
  // static to allow translation directly from query row in a view without AR model
  public static function getStatusText($status = null) {
    static $options = null;
    if($status === null)
      $status = $this->status;
    if($options === null)
      $options=self::getStatusOptions();
    return isset($options[$status]) ? $options[$status]
        : "unknown ({$status})";
  }
      
  /**
   * Create a favorite that would match this feed item
   * @return BaseFavorite an unsaved favorite
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

  /**
   * Provides information about what this item relates to
   * @return string the records item type.  Null if no relations.
   */
  public function getItemType()
  {
    if(!empty($this->tvEpisode_id))
      return 'tvEpisode';
    elseif(!empty($this->movie_id))
      return 'movie';
    elseif(!empty($this->other_id))
      return 'other';
    return null;
  }

  /**
   * Retreives the AR class of the related item type
   * @return CActiveRecord the related record. Null if no relations.
   */
  public function getItemTypeRecord()
  {
    if(null !== ($type = $this->getItemType()))
      return $this->$type;
    return null;
  }

  /**
   * Validator routine to ensure a string doesn't contain unsupported multibyte chars
   * Function from http://us2.php.net/manual/en/function.mb-detect-encoding.php
   * by chris AT w3style.co DOT uk based on code by php-note-2005 at ryandesign dot com
   * @param string the attribute to check
   * 
   * @param string $attr the string to check for multibyte characters
   * @return boolean true when the string contains no multibyte characters
   */
  public function notMultibyte($attr)
  {
    if(preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )+%xs', $this->$attr))
    {
      $this->addError($attr, "$attr contains disallowed multi-byte string");
      return false;
    }
    return true;
  }

} 
