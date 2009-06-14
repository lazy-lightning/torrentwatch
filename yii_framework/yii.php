<?php
/**
 * Yii bootstrap file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id: yii.php 433 2008-12-30 22:59:17Z qiang.xue $
 * @package system
 * @since 1.0
 */

require(dirname(__FILE__).'/YiiBase.php');

/**
 * Yii is a helper class serving common framework functionalities.
 *
 * It encapsulates {@link YiiBase} which provides the actual implementation.
 * By writing your own Yii class, you can customize some functionalities of YiiBase.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: yii.php 433 2008-12-30 22:59:17Z qiang.xue $
 * @package system
 * @since 1.0
 */
class Yii extends YiiBase
{
  /**
   * Class autoload loader.
   * This method is provided to be invoked within an __autoload() magic method.
   * This is extended to remove the @ sign hiding errors in my php after the last
   * else statement
   * @param string class name
   * @return boolean whether the class has been loaded successfully
   */
  public static function autoload($className)
  {
    // use include so that the error PHP file may appear
    if(isset(self::$_coreClasses[$className]))
      include(YII_PATH.self::$_coreClasses[$className]);
    else if(isset(self::$_classes[$className]))
      include(self::$_classes[$className]);
    else
    {
      include($className.'.php');
      return class_exists($className,false);
    }
    return true;
  }

}

spl_autoload_register(array('Yii', 'autoload'));
