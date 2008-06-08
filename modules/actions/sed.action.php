<?php
/**
 * -File        Sed.Action.php
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
        // subject is required and is the stream to be edited
        // it can be a node or a nodeset
        'subject' => '',
        // search is required and is the regular expression to match with
        // it can be a node or a nodeset
        'pattern' => '',
        // replace is optional, defaults to an empty string
        // it can be a function name, a node, or a nodeset, functions require
        // that TRUE is passed as the callback parameter
        'replacement' => '',
        // callback is optional and is boolean, defaults to FALSE
        'callback' => ''
        );

    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

		$s = Nexista_Flow::find($this->params['subject']);
		$p = Nexista_Flow::find($this->params['pattern']);
		$r = Nexista_Flow::find($this->params['replacement']);
		$c = Nexista_Flow::get($this->params['callback']);

        if(!empty($s)) {
            if($c===1) {
                $result = preg_replace_callback($p,
                            create_function(
                                '$matches',
                                'return '.$c.'($matches[0]);'
                                ),
                            $s);
            } else {
                $result = preg_replace($p,$r,$s);
            return true;
        } else {
            return false;
        }
    }
} //end class
?>