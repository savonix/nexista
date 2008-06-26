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

class Nexista_Session extends Nexista_Singleton
{

    /**
     * Session Start handler
     *
     * @var mixed
     */

    static private $_sessionStartHandler;


    /**
     * Start session
     */
    function start()
    {

        $params = Nexista_Config::getSection('session');

        $useragent = strtolower ($_SERVER['HTTP_USER_AGENT']);

        $excluded_agents = 'googlebot';
        if(!is_array($excluded_agents))
            $excluded_agents = (array)$excluded_agents;

        $nosess = false;

        foreach($excluded_agents as $xagent) {
            if (strpos($useragent,$xagent)) {
                $nosess = true;
            }
        }

        if ($params['active']==0 || $excluded_agent) {

            return false;

        } else {

            if (!is_null(self::$_sessionStartHandler)) {

                call_user_func(self::$_sessionStartHandler);

            } else {

                if (!empty($params['cookieLifetime']))
                    session_set_cookie_params($params['cookieLifetime']);
                if (!empty($params['cacheLimiter']))
                    session_cache_limiter($params['cacheLimiter']);
                if (!empty($params['cacheExpires']))
                    session_cache_expire($params['cacheExpires']);

                if (session_id() == "") session_start();
                define('NX_SESSION_ID', session_name().'='.session_id());

            }
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

    static public function registerSessionStartHandler($handler)
    {

        if (is_callable($handler))
            self::$_sessionStartHandler = $handler;
        else
            Nexista_Error::init('Session Start Handler is not callable!');

    }



}

?>