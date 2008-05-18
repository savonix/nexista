<?php
/*
Plugin Name: google_adsense_plugin.php
Plugin URI: http://www.nexista.org/
Description: Ads Google Adsense Code
Version:
Copyright: Savonix Corporation
Author: Albert Lash
License: LGPL
*/

/* TODO - Get this from the database. */
$google_code = Nexista_Config::get("./plugins/google_adsense_account/code");
if(!$priority = Nexista_Config::get("./plugins/google_adsense_account/priority")) {
    $priority = 10;
}

$google_adsense_code = <<<EOS


EOS;

$footer[] = array('string' => $google_adsense_code, 'priority' => $priority);

Nexista_Flow::add("footer",$footer,false);
?>
