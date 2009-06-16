<?php

class feed extends CActiveRecord
{
  const STATUS_NEW=0;
  const STATUS_OK=1;
  const STATUS_ERROR=2;

  /**
   * Returns the mapping of feed status integer to string
   * @return array of int=>string pairs
   */
  public function getStatusOptions()
  {
    return array(
        self::STATUS_NEW=>'Never Updated',
        self::STATUS_OK=>'Update Successful',
        self::STATUS_ERROR=>'Error connecting to feed',
    );
  }

  /**
   * @return
   */
  public function getStatusText()
  {
    $options=$this->statusOptions;
    return isset($options[$this->status]) ? $options[$this->status] : "unknown ({$this->status})";
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
		return 'feed';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('title','length','max'=>128),
			array('url','length','max'=>256),
			array('url', 'required'),
      array('status', 'default', value=>'0'),
      array('status', 'in', 'range'=>array(0, 1, 2)),

		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
        'feedItems'=>array(self::HAS_MANY, 'feedItem', 'feedItem_id'),
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
    if($this->isNewRecord)
      $this->status = self::STATUS_NEW;

    return parent::beforeValidate($type);
  }

  public function getTitle() {
    return empty($this->userTitle) ? $this->title : $this->userTitle;
  }

  /**
   * updates the database with latest items from this feed
   * some inspiration from http://simplepie.org/wiki/tutorial/how_to_display_previous_feed_items_like_google_reader
   */
  public function updateFeedItems($checkFavorites = True) {
    // id 0 is generic 'All Feeds' placeholder
    if($this->id == 0) {
      Yii::log("Skip all feeds placeholder");
      return;
    }
    // initialize the adapter

    // Chooses and returns the proper feedAapter for this feed
    $adapter = feedAdapterRouter::getAdapter($this);
    Yii::log("Initialized ".get_class($adapter)." for: {$this->url}", CLogger::LEVEL_ERROR);

    // Checks for and inserts new feed items, updates feed status, etc.
    $adapter->init();

    // Solves a memory leak in php
    $adapter->__destruct();
    unset($adapter);

    // Check for new matching favorites
    if($checkFavorites)
      Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NEW);

    // update the db with new title/description/update time and status
    $this->save();
  }

  function deleteByPk($pk, $condition='',$params=array())
  {
    if(parent::deleteByPk($pk, $condition, $params))
    {
      if(!is_array($pk))
        $pk = array($pk);

      // how can this be done using a 'WHERE feed_id IN :feed_id' clause safely?
      // $where = "feed_id IN ('".implode("', '", $a)."')";
      // but not safe at all, must be a better way
      foreach($pk as $item)
      {
        feedItem::model()->deleteAll('feed_id = :feed_id', array(':feed_id'=>$item));
        favoriteTvShow::model()->updateAll(array('feed_id'=>0), 'feed_id = :feed_id', array('feed_id'=>$item));
        favoriteMovie::model()->updateAll(array('feed_id'=>0), 'feed_id = :feed_id', array('feed_id'=>$item));
        favoriteString::model()->updateAll(array('feed_id'=>0), 'feed_id = :feed_id', array('feed_id'=>$item));
      }
      return True;
    }
    return False;
  }
}
