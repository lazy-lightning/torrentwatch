<?php

/**
 * favoriteTvShow implements validation and relations of the favoriteTvShows table
 * 
 * @uses BaseFavorite
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class favoriteTvShow extends BaseFavorite
{
  /**
   * Returns the static model of the specified AR class.
   * @param string the class to be modeled
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
   * TODO: is rules() called to create the ruleset at the right time for the min/max
   *       season/episode logic to work?
   */
  public function rules()
  {
    return array_merge(parent::rules(), array(
          array('onlyNewer', 'default', 'value'=>0),
          array('onlyNewer', 'in', 'allowEmpty'=>False, 'range'=>array(0, 1)),
          array('tvShow_id', 'exist', 'allowEmpty'=>False, 'attributeName'=>'id', 'className'=>'tvShow'),
          array('tvShow_id', 'unique', 'message'=>'A Favorite already exists for this Tv Show'),
          array('minEpisode, maxEpisode, minSeason, maxSeason', 'default', 'value'=>0),
          array('minEpisode, maxEpisode, minSeason, maxSeason', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=> 0),
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
  public function beforeValidate()
  {
    // does it have to be a new record? what if the user wanted
    // to change the title of an existing record
    if($this->isNewRecord && !is_numeric($this->tvShow_id)) {
      if(empty($this->tvShow_id))
      {
        $this->addError('tvShow_id', 'Please specify a TV Show');
      } 
      else try 
      {
        $this->tvShow_id = Yii::app()->modelFactory->tvShowByTitle($this->tvShow_id)->id;
      } 
      catch ( Exception $e) 
      {
        $this->addError("tvShow_id", "There was a problem initilizing a tvshow of title: ".$this->tvShow_id);
      }
    }

    return parent::beforeValidate();
  }

  /**
   * @return string title of the tvShow favorited
   */
  public function getName()
  {
    return $this->getRelated('tvShow')->title;
  }
}
