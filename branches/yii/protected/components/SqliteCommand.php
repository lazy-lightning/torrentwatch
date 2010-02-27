<?php

/**
 * SqliteCommand enhances CDbCommand with the ability to automatically
 * retry a command if it returns an sqlite database schema has changed error
 * generally this is due to a 'CREATE TABLE' or 'VACUUM' from this runtime
 * or even another thread
 * 
 * @uses CDbCommand
 * @package nmtdvr
 * @version $id$
 * @copyright Copyright &copy; 2009-2010 Erik Bernhardson
 * @author Erik Bernhardson <journey4712@yahoo.com> 
 * @license GNU General Public License v2 http://www.gnu.org/licenses/gpl-2.0.txt
 */
class SqliteCommand extends CDbCommand
{
  public function execute($params = array())
  {
    try
    {
      $ret = parent::execute($params);
    }
    catch (CDbException $e)
    {
      if(false !== strpos($e->getMessage(), 'General error: 17'))
      {
        // database schema has changed
        $this->cancel();
        $ret = parent::execute($params);
      } else
        throw $e;
    }
    return $ret;
  }

  /**
   * Warning: Changed yii_framework/db/CDbCommand::queryInternal from private to protected
   */
  protected function queryInternal($method, $mode, $params = array())
  {
    try
    {
      $ret = parent::queryInternal($method, $mode, $params);
    }
    catch (CDbException $e)
    {
      if(false !== strpos($e->getMessage(), 'General error: 17'))
      {
        // database schema has changed, re-prepare the query
        // sqlite cant do this on its own because it doesnt know the querys
        // sql after preparing it, but we do.
        $this->cancel();
        $ret = parent::queryInternal($method, $mode, $params);
      }
      else
        throw $e;
    }
    return $ret;
  }
}
