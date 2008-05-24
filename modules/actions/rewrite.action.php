<?php
/*
 * -File        rewrite.action.php
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
 * This method attempts to recreate a subset of mod_rewrite's greatness.
 * It takes the request URI, and then transforms it into a string of key 
 * value pairs.
 * 
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_rewriteAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'keyset' => '', // required - nodeset of keys
        'token' => '' //optional - the token separator, "/" default
        );

    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

    }

} //end class

?>