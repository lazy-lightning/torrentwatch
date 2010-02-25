<?php

/**
 * quality implements validation and relationships for the quality table
 * 
 * @uses CActiveRecord
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class quality extends CActiveRecord
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
    return 'quality';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      array('title', 'required'),
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
    );
  }

  /**
   * getCHtmlListData 
   * 
   * @param array $load if not null will be used as return value
   * @return array array suitible for building select elements (value=>Display Name)
   */
  public static function getCHtmlListData($load = null)
  {
    static $list=null;
    if($load!==null)
      $list=$load;
    if($list===null)
    {
      $list=CHtml::listData(self::model()->findAll(array('select'=>'id,title', 'order'=>'title ASC')), 'id', 'title');
      // prepend a fake empty id as -1
      $list=array('-1'=>'')+$list;
    }
    return $list;
  }
}
