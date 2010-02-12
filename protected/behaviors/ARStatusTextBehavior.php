<?php

/**
 * ARStatusTextBehavior 
 * 
 * @uses CBehavior
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class ARStatusTextBehavior extends CBehavior
{
  /**
   * getStatusOptions 
   * 
   * @return array the valid status options of Owner. (status=>text)
   */
  public function getStatusOptions() {
    $owner = $this->getOwner();
    if(method_exists($owner, 'getStatusOptions'))
      return $owner->getStatusOptions();
    $class = get_class($owner);
    return array(
        constant("$class::STATUS_NEW")=>'Unmatched',
        constant("$class::STATUS_DOWNLOADED")=>'Downloaded',
    );
  }

  /**
   * getStatusText 
   * 
   * @return string the owners status 
   */
  public function getStatusText() {
    $options = $this->getStatusOptions();
    $status = $this->Owner->status;
    return isset($options[$status]) ? $options[$status]
        : "unknown ({$status})";
  }

}

