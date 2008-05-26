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

Nexista_Flow::registerImportHandler('nx_rewrite');

function nx_rewrite($superglobals)
{
    echo "Foundry plugins are deprecated. Please fix your config file";
    // RuleID
    // RuleOrder
    // RewriteCond
    // RewriteMap
    // RewriteRule
    
    $query_string = array(
        'nid' => 'configurations',
        'package' => 'postfix',
        'page' => 'package'
        );
    if($superglobals==$_GET) {
        $gate_key = Nexista_Config::get('./build/query');
        if($superglobals[$gate_key]=="index") { 
            return $query_string;
        } else { 
            return $superglobals;
        }
    } else {
        return $superglobals;
    }
}
?>