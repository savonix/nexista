<?php
/**
 * -File        Session.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      Joshua Savage
 * -Author      Albert Lash
 *
 * PHP version 5
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@gmail.com>
 * @copyright 0000 Nexista
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

/**
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@gmail.com>
 * @copyright 0000 Nexista
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

class Nexista_Session
{

    /**
     * Hold an instance of the class
     *
     * @var object
     */

    static private $_instance;

    /** 
     * Start session
     */
     function start()
     {
        // Also check for cache_limiter - if its public, no session!
        $params = Nexista_Config::getSection('session');

        $useragent = strtolower ($_SERVER['HTTP_USER_AGENT']);

        $excluded_agent = strpos ($useragent, 'google');

        if ($params['active']==0 || $excluded_agent) {
            return false;
        } else {

            if (!empty($params['cookieLifetime']))
                session_set_cookie_params($params['cookieLifetime']);
            if (!empty($params['cacheLimiter']))
                session_cache_limiter($params['cacheLimiter']);
            if (!empty($params['cacheExpires']))
                session_cache_expire($params['cacheExpires']);

            @session_start();
            define('NX_SESSION_ID', session_name().'='.session_id());

            return true;
        }
     }

    /**
     * Registers session functions
     *
     * @param mixed $handler function or an array of class=>method
     *
     * @return null
     */

    static public function registerSessionHandler($sessionHandler)
    {
        
        if (!is_null(self::$_outputHandler)) {
            echo call_user_func(self::$_outputHandler, $this);
        } else {
        ini_set('session.save_handler', 'user');

        session_set_save_handler(
            array($handler, 'open'),
            array($handler, 'close'),
            array($handler, 'read'),
            array($handler, 'write'),
            array($handler, 'destroy'),
            array($handler, 'gc')
        );
        }
    }


    /**
     * Returns a class singleton.
     *
     * @return object class singleton instance
     */

    static public function singleton()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
}

?>