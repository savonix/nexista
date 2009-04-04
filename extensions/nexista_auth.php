<?php
/*
Extension Name: Nexista Authn Handler Plugin
Extension URI:
Description: Uses Nexista Authn
Version: 0.1
Copyright: Savonix Corporation
Author: Albert Lash
License: LGPL
*/



function nexista_authLogin($auth)
{

    if(empty($_SESSION['authReferer']))
    {
        $_SESSION['authReferer'] = $_SERVER['REQUEST_URI'];
    }
    $link_prefix = dirname(NX_LINK_PREFIX);
    $login_page = Nexista_Config::get('//nexista_auth/login');
    header('Location: '.$link_prefix.'/'.$login_page);
    exit;

}

Nexista_Auth::registerTimeoutHandler('nexista_authLogin');
Nexista_Auth::registerLoginHandler('nexista_authLogin');
Nexista_Auth::registerDeniedHandler('nexista_authLogin');
Nexista_Auth::registerExpiredHandler('nexista_authLogin');
