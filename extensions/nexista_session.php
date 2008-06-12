<?php

/**
 * Extension Name: Session Handler
 * Extension URI:
 * Description: Uses HTTP_Session2 to manage sessions
 * Version: 0.1
 * Copyright: Nexista
 * Author: Albert Lash
 * License: LGPL
 * Status: beta
 *
 * PHP version 5
 *
 * @category Nexista_Extensions
 * @package  Nexista_Session
 * @author   Albert Lash <albert.lash@gmail.com>
 * @license  http://www.gnu.org LGPL
 * @version  SVN: 123
 * @link     http://www.nexista.org/
 *
 */

/*
Configuration:

<nexista_cache>
    <placement>prepend</placement>
    <source>&includepath;extensions/nexista_cache.php</source>
    <active>1</active>
    <timer_comment>1</timer_comment>
    <excludes></excludes>
    <purge_gates>list-of-gates,logout</purge_gates>
    <purge_gets>from_date</purge_gets>
</nexista_cache>
*/

include('HTTP/Session2.php');
Nexista_Session::registerSessionStartHandler(array('HTTP_Session2','start'));


/* Get this from config
HTTP_Session2::setContainer('MDB2',
    array('dsn' => 'mysql://root:password@localhost/database',
    'table' => 'sessiondata'));
*/

?>
