<?php
/*
Plugin Name: Nexista Authn Handler Plugin
Plugin URI:
Description: Uses Nexista Authn
Version: 0.1
Copyright: Savonix Corporation
Author: Albert Lash
License: LGPL
*/



function authLogin($auth)
{

    if(empty($_SESSION['authReferer']))
    {
        $_SESSION['authReferer'] = $_SERVER['REQUEST_URI'];
    }
    $link_prefix = dirname(NX_LINK_PREFIX);
    header("Location: ".$link_prefix."/auth.php?nid=login");
    exit;

}

Nexista_Auth::registerTimeoutHandler('authLogin');
Nexista_Auth::registerLoginHandler('authLogin');
Nexista_Auth::registerDeniedHandler('authLogin');
Nexista_Auth::registerExpiredHandler('authLogin');
