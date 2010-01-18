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
      array('lastUpdated', 'default', 'setOnEmpty'=>false, 'value'=>time()),
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
  public function generateFavorite($feedItem)
  {
    $fav=new favoriteMovie;
    $fav->rating = empty($this->rating) ? 100 : $this->rating;
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

  public function getStatusOptions()
  {
    return array(
        self::STATUS_NEW        => 'Unmatched Movie',
        self::STATUS_DOWNLOADED => 'Downloaded',
    );
  }

  public function getStatusText()
  {
    $options=$this->getStatusOptions();
    return isset($options[$this->status]) ? $options[$this->status]
              : "unknown ({$this->status})";
  }
}
