<?php
/*
 * -File        email.plugin.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Savonix Corporation
 * -Author		Albert Lash
 */

/**
 * @package     Nexista Auth
 * @subpackage  Plugins
 * @author      Albert Lash
 */
 
/**
 * This action sends an email and uses the PEAR Net_SMTP package.
 *
 * @package     Nexista Auth
 * @subpackage  Plugins
 */

class Nexista_EmailAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'recipient' => '', //required - 
        'sender' => '', //required - 
        'subject' => '', //optional - 
        'message' => '', //optional - 
        'server' => '',
        'port' => '',
        'authentication' => ''
        
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {
        //$smtp = new Net_SMTP('ssl://mail.example.com', 465);
    }
} //end class

?>