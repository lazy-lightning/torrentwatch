<?php

/**
 * favoriteMovie implements the validation rules and relations of the favoriteMovies table
 * 
 * @uses BaseFavorite
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class favoriteMovie extends BaseFavorite
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
    return 'favoriteMovies';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array_merge(parent::rules(), array(
      array('name', 'required'),
      array('minYear', 'default', 'value'=>1900),
      array('maxYear', 'default', 'value'=>2020),
      array('rating', 'default', 'value'=>0),
      array('genre_id', 'exist', 'allowEmpty'=>false, 'attributeName'=>'id', 'className'=>'genre'),
      array('minYear, maxYear', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=>1900, 'max'=>2100),
      array('rating', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'min'=>0, 'max'=>100),
    ));
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
        'feed' => array(self::BELONGS_TO, 'feed', 'feed_id'),
        'quality' => array(self::MANY_MANY, 'quality', 'favoriteMovies_quality(favoriteMovies_id, quality_id)'),
        'genre' => array(self::BELONGS_TO, 'genre', 'genre_id'),
    );
  }

  /**
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels()
  {
    return array(
      'id'=>'Id',
      'name'=>'Name',
      'genre_id'=>'Genre',
      'quality_id'=>'Quality',
      'rating'=>'Rating >=',
    );
  }

}
