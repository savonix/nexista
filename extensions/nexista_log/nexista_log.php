<?php
/*
Plugin Name: Nexista Log
Plugin URI:
Description:
Version: 0.1
Copyright: Nexista
Author: Albert Lash
License: LGPL
*/


/* PEAR LOG */

require_once 'Log.php';

$file = &Log::factory('file', '/tmp/out.log', 'TEST');
$file->log('Logging to out.log.');

/* END LOGGER */



?>
