<?php
/**
 * -File        Server.Action.php
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
 * This action strips unwanted characters from a string.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_serverAction extends Nexista_Action
{

    /* NOTE: This action is deprecated. Use the _server flow nodeset instead */
    /* It can be activated by including "V" in the flow vars in config.xml */
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