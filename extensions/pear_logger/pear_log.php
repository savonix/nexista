<?php
/*
Plugin Name: Development Output Buffer
Plugin URI:
Description:
Version: 0.1
Copyright: Nexista
Author: Albert Lash
License: LGPL
*/


/* START LOG */

require_once 'Log.php';

$file = &Log::factory('file', '/tmp/out.log', 'TEST');
$file->log('Logging to out.log.');

/* END LOGGER */



?>
