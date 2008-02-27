<?php
/*
 * -File        header.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2008, Savonix Corporation
 * -Author      Albert Lash
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Albert Lash
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
        $value = $this->params['value'];
		header($header.": ".$value);
            

    }
} //end class

?>