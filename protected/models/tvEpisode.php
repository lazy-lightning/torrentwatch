<?php

/**
 * tvEpisode implements validation and relationships for the tvEpisode table
 * 
 * @uses CActiveRecord
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class tvEpisode extends CActiveRecord
{

  const STATUS_NEW = 0;
  const STATUS_DOWNLOADED = 1;

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
   * Returns a list of behaviors that this model should behave as.
   * @return array the behavior configurations (behavior name=>behavior configuration)
   */
  public function behaviors()
  {
    return array(
        'statusText'=>'ARStatusTextBehavior',
    );
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      array('tvShow_id', 'exist', 'allowEmpty'=>false, 'attributeName'=>'id', 'className'=>'tvShow'),
      array('lastTvdbUpdate, lastUpdated', 'default', 'value'=>0),
      array('lastUpdated, lastTvdbUpdate, season, episode', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=>0),
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
        'feedItem'=>array(self::HAS_MANY, 'feedItem', 'tvEpisode_id'),
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
   * @return favoriteMovie a favoriteMovie object to match this episode
   */
  public function generateFavorite($feedItem) 
  {
    $fav = new favoriteTvShow;
    $fav->tvShow_id = $this->tvShow_id;
    $fav->onlyNewer = 1;
    return $fav;
  }

  public function getFavorite()
  {
    $favoriteId = $this->dbConnection->createCommand(
          'SELECT favoriteTvShows_id FROM matchingFavoriteTvShows'.
          ' WHERE tvEpisode_id = :id'
    )->bindValue(':id', $this->getAttribute('id'))->queryScalar();
    if($favoriteId)
      return favoriteTvShow::model()->findByPk($favoriteId);
    return false;
  }

  /**
   * @return string a string representation of this records episode
   */
  public function getEpisodeString($s=null,$e=null)
  {
    if($s === null || $e === null)
    {
      $e = $this->episode;
      $s = $this->season;
    }

    if($e > 10000) 
      return $this->getEpisodeDateString($e);
    // oddly faster than sprintf
    if($s<10)
      $s = '0'.$s;
    if($e == 0)
      return "S${s}E??";
    if($e<10)
      return "S${s}E0${e}";
    return "S${s}E${e}";
  }

  protected function getEpisodeDateString($time)
  {
    static $utc = null;
    if($utc === null)
      $utc = new DateTime('Jan 1 1970', new DateTimeZone('UTC'));
    $date = clone $utc;
    $date->modify('+'.$time.'s');
    return $date->format('Y-m-d');
  }

}
