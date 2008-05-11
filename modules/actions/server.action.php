<?php
/*
 * -File        server.action.php
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
 * This action strips unwanted characters from a string.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_serverAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'exclude' => '' //optional - variables to exclude - not active
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

		Nexista_Flow::add("_SERVER",$_SERVER,false);

    }
} //end class
?>