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

}
