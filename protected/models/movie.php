<?php

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
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      array('status', 'default', 'value'=>self::STATUS_NEW),
      array('imdbId', 'numerical', 'integerOnly'=>true),
    );
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
      'genres'=>array(self::MANY_MANY, 'genre', 'movie_genre(movie_id, genre_id)'),
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
      'imdbId'=>'Imdb ',
    );
  }

  /**
   * @return favoriteMovie a favoriteMovie object to match this movie
   */
  public function generateFavorite()
  {
    $fav=new favoriteMovie;
    $fav->rating = empty($feedItem->movie->rating) ? 100 : $feedItem->movie->rating;
    $fav->genre_id = $feedItem->movie->genres[0]->id;
    $fav->name = $feedItem->movie->genres[0]->title.' - '.$feedItem->qualityString;
    if(!empty($feedItem->movie->year))
    {
      $fav->minYear = $feedItem->movie->year - 5;
      $fav->maxYear = $feedItem->movie->year + 5;
    }
    return $fav;
  }

  /**
   * @return string url to imdb page
   */
  public function getImdbLink()
  {
    return 'http://www.imdb.com/title/tt'.$this->imdbId;
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
}
