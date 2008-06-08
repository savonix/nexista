<?php
/**
 * -File        Log.Action.php
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
 * This action uses PEAR Log to enable logging.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_logAction extends Nexista_Action
{


    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'code' => '',   //required - 3 letter action code
        'target' => ''  //optional - target ID/name of action
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {
        // Use PEAR Log
        return true;
    }



} //end class

?>