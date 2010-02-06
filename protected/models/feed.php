<?php

class feed extends CActiveRecord
{
  const STATUS_NEW=0;
  const STATUS_OK=1;
  const STATUS_ERROR=2;

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
      array('lastUpdated', 'default', 'setOnEmpty'=>false, 'value'=>time()),
      array('downloadType', 'in', 'allowEmpty'=>false, 'range'=>array_keys(feedItem::getDownloadTypeOptions())),
      array('url', 'url', 'allowEmpty'=>false),
      array('status', 'default', 'value'=>self::STATUS_NEW),
      array('status', 'in', 'allowEmpty'=>false, 'range'=>array_keys($this->getStatusOptions())),
      array('title', 'safe'),
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

  public function afterSave() {
    parent::afterSave();
    if($this->isNewRecord)
    {
      // force to false so the save from in updateFeedItems doesn't fail
      $this->isNewRecord = False;
      // also set the old pk so it doesnt update a null row
      $this->setPrimaryKey($this->getPrimaryKey());
      $this->updateFeedItems();
      $this->refresh();
    }
  }

  /**
   * delete related feed items and repoint any favorites to the generic all feeds id
   * @return boolean successfull delete
   */
  public function deleteByPk($pk, $condition='',$params=array())
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
        tvEpisode::model()->deleteAll(
            'id NOT IN (SELECT tvEpisode_id id FROM feedItem WHERE id NOT NULL) AND status = :status', 
            array(':status'=>tvEpisode::STATUS_NEW)
        );
        movie::model()->deleteAll(
            'id NOT IN (SELECT movie_id id FROM feedItem WHERE id NOT NULL) AND status = :status', 
            array(':status'=>movie::STATUS_NEW)
        );
        other::model()->deleteAll(
          'id NOT IN (SELECT other_id id FROM feedItem WHERE id NOT NULL) AND status = :status', 
          array(':status'=>other::STATUS_NEW)
        );
      }
      return True;
    }
    return False;
  }

  /**
   * Returns the mapping of feed status integer to string
   * @return array of int=>string pairs
   */
  public static function getStatusOptions()
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
   * @return string the title of this feed, prefering a user provided title
   */
  public function getTitle() {
    return empty($this->userTitle) ? $this->title : $this->userTitle;
  }

  /**
   * updates the database with latest items from this feed
   * some inspiration from http://simplepie.org/wiki/tutorial/how_to_display_previous_feed_items_like_google_reader
   * @return none
   */
  public function updateFeedItems($checkFavorites = True) {
    // id 0 is generic 'All Feeds' placeholder
    if($this->id == 0) {
      return;
    }

    // Chooses and returns the proper feedAapter for this feed
    $adapter = feedAdapterRouter::getAdapter($this);
    Yii::log("Initialized ".get_class($adapter)." for: {$this->url}");

    // Checks for and inserts new feed items, updates feed status, etc.
    $adapter->init();

    // Solves a memory leak in php
    $adapter->__destruct();
    unset($adapter);

    // Check for new matching favorites
    if($checkFavorites)
      Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NEW);

    // update the db with new title/description/update time and status
    // Is this save necessary always?  on a run with no downloading and no
    // updates this used 25% of the execution time.
    $trans = Yii::app()->db->beginTransaction();
    try {
        $this->save();
        $trans->commit();
    } catch (Exception $e) {
        $trans->rollback();
        throw $e;
    }
  }

  public static function getCHtmlListData($load = null)
  {
    static $list=null;
    if($load!==null)
      $list=$load;
    if($list===null)
    {
      $list=CHtml::listData(self::model()->findAll(array('select'=>'id,title')), 'id', 'title');
    }
    return $list;
  }

}
