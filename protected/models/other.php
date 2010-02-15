<?php

/**
 * other implements validation and relationships for the other table
 * 
 * @uses CActiveRecord
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class other extends CActiveRecord
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
    return 'other';
  }

  /**
   * Returns a list of behaviors that this model should behave as.
   * @return array the behavior configurations (behavior name=>behavior configuration)
   */
  public function behaviors()
  {
    return array(
        'statusText'=>'ARStatusTextBehavior'
    );
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
        array('status', 'default', 'value'=>self::STATUS_NEW),
        array('lastUpdated', 'default', 'setOnEmpty'=>false, 'value'=>time()),
        array('lastImdbUpdate', 'default', 'value'=>0),
    );
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
        'feedItem'=>array(self::HAS_MANY, 'feedItem', 'other_id'),
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
    return false;
  }
}
