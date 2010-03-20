<?php

/**
 * movie implements validation and relationships for the movie table
 * 
 * @uses CActiveRecord
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class movie extends CActiveRecord
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
    return 'movie';
  }

  /**
   * Returns a list of behaviors that this model should behave as.
   * @return array the behavior configurations (behavior name=>behavior configuration)
   */
  public function behaviors()
  {
    return array(
        'statusText' => 'ARStatusTextBehavior',
    );
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      array('status', 'default', 'value'=>self::STATUS_NEW),
      array('lastUpdated, lastImdbUpdate', 'default', 'value'=>0),
      array('lastUpdated, lastImdbUpdate', 'numerical', 'integerOnly'=>true, 'min'=>0),
    );
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
      'genres'=>array(self::MANY_MANY, 'genre', 'movie_genre(movie_id, genre_id)'),
      'feedItem'=>array(self::HAS_MANY, 'feedItem', 'movie_id'),
    );
  }

  /**
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels()
  {
    return array(
      'id'=>'Id',
      'title'=>'Title',
    );
  }

  public function getFavorite()
  {
    $favoriteId = $this->dbConnection->createCommand(
          'SELECT favoriteMovies_id FROM matchingFavoriteMovies'.
          ' WHERE movie_id = :id'
    )->bindValue(':id', $this->id)->queryScalar();
    if($favoriteId)
      return favoriteMovie::model()->findByPk($favoriteId);
    return false;
  }

  /**
   * @return favoriteMovie a favoriteMovie object to match this movie
   */
  public function generateFavorite($feedItem)
  {
    $fav=new favoriteMovie;
    $fav->rating = empty($this->rating) ? 0 : $this->rating;
    $fav->genre_id = $this->genres[0]->id;
    $fav->name = $this->genres[0]->title.' - '.$feedItem->qualityString;
    if(!empty($this->year))
    {
      $fav->minYear = $this->year - 5;
      $fav->maxYear = $this->year + 5;
    }
    return $fav;
  }

  /**
   * @return string genres in 'A / B / C' format
   */
  public function getGenreString()
  {
    $string = array();
    foreach($this->genres as $genre) { 
      $string[] = $genre->title;
    }
    return implode(' / ', $string);
  }

  /**
   * @return string prefered title of the movie
   */
  public function getFullTitle()
  {
    return empty($this->name) ? $this->title : $this->name;
  }

  /**
   * @return array the status options (status code=>status name)
   */
  public function getStatusOptions()
  {
    return array(
        self::STATUS_NEW        => 'Unmatched Movie',
        self::STATUS_DOWNLOADED => 'Downloaded',
    );
  }

  /**
   * @return string this items status as a string
   */
  public function getStatusText()
  {
    $options=$this->getStatusOptions();
    return isset($options[$this->status]) ? $options[$this->status]
              : "unknown ({$this->status})";
  }
}
