<?php
/*
 * -File        access.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      Albert Lash
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Albert Lash
 */
 
/**
 * This is similar to Apache's mod_access, but not as capable.
 * http://pear.php.net/manual/en/package.networking.net-ipv4.ipinnetwork.php
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_AccessAction_ extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'allow' => '' //required - name of ip addresses to allow
        'deny' => '' //required - name of ip addresses to deny
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

		$res = Nexista_Flow::find($this->params['var']);
        $remote_ip = $_SERVER['REMOTE_ADDR'];
        if($res->length === 1)
        {

            return true;
        }

        return false;


    }
} //end class

?>
