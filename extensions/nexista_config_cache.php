<?php
/**
 * -File        nexista_config_cache.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Savonix Corporation
 * -Author      Albert Lash
 *
 * PHP version 5
 *
 * @category Nexista_Extensions
 * @package  Nexista_Builder
 * @author   Albert Lash <albert.lash@savonix.com>
 * @license  http://www.gnu.org LGPL
 * @version  SVN: 123
 * @link     http://www.nexista.org/
 */

/*
Extension Name:
Extension URI:
Description:
Version:
Copyright: Savonix Corporation
Author: Albert Lash
License: LGPL
*/



$config_cache = NX_PATH_COMPILE.$_SERVER['HTTP_HOST'].'_config_cache.php';

if(is_file($config_cache)) {
    Nexista_Flow::add('config_cache','true');
    include($config_cache);
    Nexista_Flow::add('site_config',$config);
}
?>
