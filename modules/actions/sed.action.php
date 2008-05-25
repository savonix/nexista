<?php
/*
 * -File        sed.action.php
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
 * This action is based on the idea of a stream editor (sed) but uses the php
 * command preg_replace_callback. It provides a regular expression search and
 * replace service for strings and arrays, and can also replace matched string
 * with the output of a function performed on the matched string itself.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_sedAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'search' => '',        //required - string, node, or nodeset
        'replace' => ''   //required - string, node, or nodeset
        'callback' => ''   //optional - callback function
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