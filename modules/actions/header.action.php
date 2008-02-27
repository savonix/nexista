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
 * This action concatenates params
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class HeaderAction extends Action
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