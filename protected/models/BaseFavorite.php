<?php

/**
 * BaseFavorite is the base class providing most features needed by favorite objects
 * 
 * BaseFavorite provides validation of common data and triggering of downloads
 *
 * @uses CActiveRecord
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
abstract class BaseFavorite extends CActiveRecord
{

  /**
   * returns array to instantiate class behaviors
   * @return array the behavior configurations (behavior name=>behavior configuration)
   */
  public function behaviors()
  {
    return array(
        'quality' => array('class'=>'ARQualityBehavior'),
    );
  }

  /**
   * Returns the validation rules for attributes
   * @return array validation rules to be applied when {@link CModel::validate()} is called.
   */
  public function rules()
  {
    return array(
        array('queue', 'default', 'value'=>1),
        array('feed_id', 'default', 'value'=>0),
        array('saveIn', 'writableDirectory'),
        array('feed_id', 'exist', 'allowEmpty'=>False, 'attributeName'=>'id', 'className'=>'feed'),
        array('queue', 'in', 'allowEmpty'=>False, 'range'=>array(0,1)),
    );
  }

  /**
   * Check for any newly matching favorites on successfull save
   * @return void
   */
  public function afterSave()
  {
    parent::afterSave();
    Yii::app()->dlManager->checkFavorites(feedItem::STATUS_NOMATCH);
  }

  /**
   * Reset any currently matching feed Items that arn't downloaded to nomatch
   * @return boolean if save should proceede
   */
  public function beforeSave()
  {
    if($this->isNewRecord === False) {
      Yii::app()->dlManager->resetMatching($this);
    }
    return parent::beforeSave();
  }

  /**
   * Clean out many_many relationship tables on delete
   * @return boolean true on successfull delete
   */
  public function deleteByPk($pk,$condition='',$params=array())
  {
    if(parent::deleteByPk($pk, $condition, $params))
    {
      $table = $this->tableName();
      $class = $table.'_quality';
      $model = new $class;
      $model->deleteAll($table.'_id = :id', array(':id'=>$pk));
      return True;
    }
    return False;
  }

  /**
   * Validation function
   * @return void
   */
  public function writableDirectory($attribute, $params) {
    if(!empty($this->$attribute) &&
       False == (is_dir($this->$attribute) && is_writable($this->$attribute)))
      $this->addError($attribute, $this->$attribute." is not a writable directory");
  }
}

