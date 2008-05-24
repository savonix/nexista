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
        $query_string = array(
                'nid' => 'configurations',
                'package' => 'postfix',
                'page' => 'package'
                );
        $my_node_list = Nexista_Flow::find("//_get");
        $my_node = $my_node_list->item(0);
        foreach($query_string as $key => $value) {
            $my_node_check = Nexista_Flow::find("//_get/nid");
            Nexista_Flow::delete($my_node_check->item(0));
            Nexista_Flow::add($key,$value,$my_node);
        }
    }

} //end class

?>