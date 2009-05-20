<?php
/*
 * -File        nexista_rewrite.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Savonix Corporation
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

Nexista_Flow::registerImportHandler('nexista_rewrite');

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

    // Configuration: 
    /*
<net_rewrite>
    <rewrite_base></rewrite_base>
    <rewrite_map></rewrite_map>
    <rewrite>
        <description>This rule will match if the domain name being accessed
        is 4.2.2.1, and if so, it will return a 403 (FORBIDDEN RESPONSE).
        </description>
        <cond test="REQUEST_URI" pattern="index"/>
        <cond test="REQUEST_METHOD" pattern="POST"/>
        <rule pattern="{//path_prefix}(.*)" substitution="{//path_prefix}" flags="L"/>
    </rewrite>
    <rewrite>
        <description>This rule will match if the domain name being accessed
        is 4.2.2.1, and if so, it will return a 403 (FORBIDDEN RESPONSE).
        </description>
        <cond test="HTTP_HOST" pattern="4.2.2.1"/>
        <rule pattern="." substitution="-" flags="F"/>
    </rewrite>
</net_rewrite>
    */
    // For each rewrite:
        // Process Conditions
        // Process Rules
        // If matched, continue.

    if($superglobals==$_GET) {
        // gate_key is usually "nid", but since its configurable, not hard coded
        $gate_key = Nexista_Config::get('./build/query');
        if($superglobals[$gate_key]=='index' || $superglobals[$gate_key]=='') {
            // need to replace 
            $superglobals[$gate_key] = 'configurations';
            $superglobals['barf'] = 'true';
        }
    }
    return $superglobals;
}
?>