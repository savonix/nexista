<?php
/**
 * -File        singleton.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Savonix Corporation
 * -Author      Joshua Savage
 * -Author      Albert Lash
 *
 * PHP version 5
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

/**
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

class Nexista_Singleton
{

    /**
     * Hold an array of class instances
     *
     * @var object
     */

    private static $_instance_array = array();

    /**
     * Returns a class singleton.
     *
     * @return object class singleton instance
     */


    public static function singleton($class_name)
    {
        if (!isset(self::$_instance_array[$class_name])) {
            self::$_instance_array[$class_name] = new $class_name();
        }
        return self::$_instance_array[$class_name];

    }
}

?>