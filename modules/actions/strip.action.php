<?php
/**
 * -File        Strip.Action.php
 * -Copyright   Savonix Corporation
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

class Nexista_stripAction extends Nexista_Action
{

    /* NOTE: This action is deprecated, use sed.action.php instead */
    
    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'var' => '',        //required - flow var
        'chars' => ''   //optional - array of chars to strip
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

		$res = Nexista_Flow::find($this->params['var']);
        if(!empty($res)) {
            $chars = Nexista_Flow::getByPath($this->params['chars']);
            $res->item(0)->nodeValue = str_replace($chars,"",$res->item(0)->nodeValue);

            return true;
        } else {
            return false;
        }
    }
} //end class
?>