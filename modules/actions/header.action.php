<?php
/**
 * -File        Header.Action.php
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
 * This action sends an http header.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_HeaderAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'header' => '', //required
        'value' => '' //required
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {
        $header = $this->params['header'];
        $value = Nexista_Path::parseInlineFlow($this->params['value']);
        // Can't use literal commas in the params section
        $value = str_replace(';',',',$value);
		header($header.': '.$value);
    }
} //end class

?>