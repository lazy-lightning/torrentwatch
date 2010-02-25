<?php

/**
 * many of the ar classes have a many_many relationship
 * with the quality table.  This standardizes their setting
 * and getting
 * 
 * @uses CBehavior
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class ARQualityBehavior extends CBehavior 
{
  private $_qualityIds;

  /**
   * Declares events and the corresponding event handler methods.
   * @return array events (array keys) and the corresponding event handler methods (array values).
   */
  public function events()
  {
    return array(
        'onAfterSave' => 'afterSave',
    );
  }

  /**
   * getQualityIds 
   * 
   * @return array 0 indexed array of quality ids related to this record
   */
  public function getQualityIds() 
  {
    if($this->_qualityIds === null) 
    {
      $table = $this->Owner->tableName();
      $class = $table.'_quality';
      $id = $table.'_id';
      $relations = CActiveRecord::model($class)->findAllByAttributes(
          array($id => $this->Owner->id)
      );

      $this->_qualityIds = array();
      foreach($relations as $record) 
      {
        $this->_qualityIds[] = $record->quality_id;
      }
    }
    return $this->_qualityIds;
  }

  /**
   * getQualityString 
   * 
   * @return string ' / ' seperated list of related qualitys
   */
  public function getQualityString() 
  {
    $string = array();
    foreach($this->Owner->quality as $quality) 
    {
      $string[] = $quality->title;
    }
    return implode(' / ', $string);
  }

  /**
   * Set the records quality to be all positive numbers in the given set
   * 
   * @param array $in the quality ids to be set
   * @return void
   */
  public function setQualityIds($in) 
  {
    $this->_qualityIds = array();
    foreach($in as $val) 
    {
      if($val >= 0)
        $this->_qualityIds[] = $val;
    }
  }

  /**
   * Save quality relations to the database after successfull save.
   * 
   * @param CEvent $event the event that triggered this method
   * @return void
   */
  public function afterSave($event) 
  {
    // update scenario
    // Clean out any quality relations if this isn't new
    $table = $this->Owner->tableName();
    $class = $table.'_quality';
    $id = $table.'_id';
    // Cache the quality ids first if we dont have any
    $qualityIds = $this->getQualityIds();
    if(!$this->Owner->isNewRecord) 
    {
      CActiveRecord::model($class)->deleteAll($id.'=:id', array(':id'=>$this->Owner->id));
    }

    // set quality relations
    foreach($qualityIds as $qualityId) 
    {
      $relation = new $class;
      $relation->$id = $this->Owner->id;
      $relation->quality_id = $qualityId;
      $relation->save();
    }
  }
}

