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

function nexista_rewrite($superglobals)
{

    // RuleID
    // RuleOrder

    // RewriteMap - its really needed for this to be really useful.

    // RewriteCond TestString CondPattern
    // TestString can be a string or an xpath query, CondPattern can be eregi
    // Example: "RewriteCond {//_server/REQUEST_URI} blahblah
    // (the xpath query value would be fetched in the compiled gate
    // if unsuccessful, return false and break

    // RewriteRule Pattern Substition [flags]
    // Pattern is a regular preg_match 
    // Only a subset of flags supported
    // Supported flags:
    // * CO - Cookie
    // * L - Last
    // * R - Redirect
    // * E - Env
    // * F - Forbidden

    // Example:
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