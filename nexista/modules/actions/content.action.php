<?php
/*
 * -File        content.action.php
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
 * NOTE: This action is deprecated. Use header.action.php instead.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_ContentAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'mime' => '' //required
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {   
        $contentType = $this->params['mime'];
		header("Content-Type: ".$contentType);
            

    }
} //end class

?>