<?php

/**
 * favoriteMovies_quality implements an AR class for the favoriteMovies 
 * quality MANY_MANY relation
 * 
 * @uses CActiveRecord
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class favoriteMovies_quality extends CActiveRecord
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
    return 'favoriteMovies_quality';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      array('favoriteMovies_id, quality_id', 'required'),
      array('favoriteMovies_id, quality_id', 'numerical', 'integerOnly'=>true),
    );
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
    );
  }

  /**
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels()
  {
    return array(
      'favoriteMovies_id'=>'Favorite Movies',
      'quality_id'=>'Quality',
    );
  }
}
