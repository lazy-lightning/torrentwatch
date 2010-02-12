<?php

/**
 * favoriteTvShows_quality implements an AR class for the MANY_MANY relation
 * between favoriteTvShows and quality
 * 
 * @uses CActiveRecord
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class favoriteTvShows_quality extends CActiveRecord
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
    return 'favoriteTvShows_quality';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
        array('favoriteTvShows_id, quality_id', 'required'),
        array('favoriteTvShows_id, quality_id', 'numerical', 'integerOnly'=>true),
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
      'favoriteTvShows_id'=>'Favorite Tv Shows ',
      'quality_id'=>'Quality ',
    );
  }
}
