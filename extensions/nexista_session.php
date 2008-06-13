<?php

/**
 * Extension Name: Session Handler
 * Extension URI:
 * Description: Uses HTTP_Session2, memcache to manage sessions
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

<nexista_session>
    <placement>prepend</placement>
    <source>&includepath;extensions/nexista_session.php</source>
</nexista_session>
*/

if(1==2) {
    ini_set('session.save_handler','user');
    include('HTTP/Session2.php');
    Nexista_Session::registerSessionStartHandler(array('HTTP_Session2','start'));
} elseif (class_exists('Memcacxhe')) {
    ini_set('session.save_handler','memcache');
    ini_set('session.save_path', 'tcp://192.168.3.3:11211?persistent=1&weight=2,tcp://memcached1.private.savonix.com:11211?persistent=1&weight=2&timeout=2&retry_interval=4,tcp://memcached2.private.savonix.com:11211?persistent=1&weight=2&timeout=2&retry_interval=4');
} else {
    ini_set('session.save_handler','files');
}

/* Get this from config
HTTP_Session2::setContainer('MDB2',
    array('dsn' => 'mysql://root:password@localhost/database',
    'table' => 'sessiondata'));
*/

?>
