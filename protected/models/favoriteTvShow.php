<?php

class favoriteTvShow extends ARwithQuality
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
		return array(
		);
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
		);
	}

  public function afterSave()
  {
    parent::afterSave();
    Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NOMATCH);
  }

  public function beforeSave()
  {
    // reset any currently matching feed Items that arn't downloaded to nomatch
    if($this->isNewRecord === False) {
      $this->dbConnection->createCommand(
          'UPDATE feedItem SET status='.feedItem::STATUS_NOMATCH.
          ' WHERE feedItem.id IN ( SELECT feedItem_id as id FROM matchingFavoriteTvShows m'.
                                  ' WHERE m.favoriteTvShows_id = '.$this->id.
                                  '   AND m.feedItem_status NOT IN ("'.
                                    feedItem::STATUS_AUTO_DL.'", "'.feedItem::STATUS_MANUAL_DL.'"));'
      )->execute();
    }
    return parent::beforeSave();
  }

  public function beforeValidate($type)
  {
    if($this->isNewRecord && !is_numeric($this->tvShow_id)) {
      try {
        $this->tvShow_id = factory::tvShowByTitle($this->tvShow_id)->id;
        Yii::log('Set tvShow_id to '.$this->tvShow_id, CLogger::LEVEL_ERROR);
      } catch ( Exception $e) {
        $this->addError("tvShow_id", "There was a problem initilizing a tvshow of that title");
        Yii::log('Failed adding tvShow for new favorite validation: '.$e->error, CLogger::LEVEL_ERROR);
      }
    }
    if(!empty($this->saveIn)) {
      if(!is_dir($this->saveIn))
      {
        $this->addError('saveIn', "Save In must be a valid directory");
      }
    }
    return parent::beforeValidate($type);
  }

  public function deleteByPk($pk,$condition='',$params=array())
  {
    if(parent::deleteByPk($pk, $condition, $params))
    {
      favoriteTvShows_quality::model()->deleteAll('favoriteTvShows_id = :id', array(':id'=>$pk));
      return True;
    }
    return False;
  }

  public function getName()
  {
    return $this->tvShow->title;
  }
}
