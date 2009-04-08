<?php
/* <!--
Plugin Name: jQuery Extension
Plugin URI:
Description:
Version: 0.1
Copyright: Savonix Corporation
Author: Albert Lash
License: LGPL

--> */




$flow = Nexista_Flow::singleton('Nexista_Flow');

$mylink = $_SERVER['SCRIPT_NAME'];
if ($_GET['nxrw_path']) {
    $mylink = $_GET['nxrw_path'];
}
$my_script = '<script type="text/javascript" src="'.$mylink.'?nid=x-jquery.js">&#160;</script>';

$f = new DOMDocument('1.0', 'UTF-8');
$f->loadXML('<head_nodes><priority>100</priority><nodes>'.$my_script.'</nodes></head_nodes>');
$n = $f->getElementsByTagName('head_nodes')->item(0);
$g = $flow->flowDocument->importNode($n, true);
$flow->root->appendChild($g);

?>
