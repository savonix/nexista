<?php
/**
 * -File        nexista_builder.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Savonix Corporation
 * -Author      Albert Lash
 *
 * PHP version 5
 *
 * @category Nexista_Extensions
 * @package  Nexista_Builder
 * @author   Albert Lash <albert.lash@gmail.com>
 * @license  http://www.gnu.org LGPL
 * @version  SVN: 123
 * @link     http://www.nexista.org/
 */

/*
Plugin Name:
Plugin URI:
Description:
Version:
Copyright: Savonix Corporation
Author: Albert Lash
License: LGPL
*/
if (isset($nexista_path)) {
    define('INCLUDE_PATH', $nexista_path);
} else {
    define('INCLUDE_PATH', "/usr/share/php/");
}

if (!defined('SERVER_NAME')) {
    define('SERVER_NAME', $server_name);
}
if (!file_exists(INCLUDE_PATH.'kernel/foundry.php')) {
    echo "I can't find the nexista foundry class, and cannot continue. Try this:
    <br/><br/><a href='http://www.nexista.org'>http://www.nexista.org</a>, 
    and so you know, I looking here: <br/>";
    echo INCLUDE_PATH."kernel/foundry.php";;
    exit;
} else {
    require INCLUDE_PATH.'kernel/foundry.php';
}

$foundry = Nexista_Foundry::singleton();

$config     = PROJECT_ROOT.'/config/config.xml';
$app_config = PROJECT_ROOT.'/apps/'.APP_NAME.'/config/config.xml';


function Nexista_Check_freshness()
{
    global $config;
    global $app_config;
    global $foundry;
    global $server_init;

    $my_sitemap = PROJECT_ROOT.'/apps/'.APP_NAME.'/sitemap.xml';

    $last_build_time = filemtime($server_init);

    if (file_exists($app_config)) {
        if ($last_build_time < filemtime($app_config)
            || $last_build_time < filemtime($my_sitemap)) {
            $app_config_stat = false;
        } else {
            $app_config_stat = true;
        }
    }

    if ($last_build_time < filemtime($my_sitemap) || 
        $last_build_time < filemtime($config) || 
        $app_config_stat === false) {

        nexista_build_it_now();

        if ($foundry->debug == 1) {
            echo "Nexista is rebuilding the loader because either the sitemap 
            or one of the configs has been edited.<br/>
            <a href='javascript:history.go(-1)'>OK, all done, go back.</a><br/>
            Building index file....OK<br/>";
        }
    }
}

function Nexista_Build_It_now()
{
    global $config;
    global $foundry;
    global $server_init;
    ob_end_clean();
    header('Cache-Control: no-cache, no-store');
    ?>
    
    <html>
    <body style="padding: 150px; font-family: verdana;">
    <?php
    echo "Looks like you are installing to $server_init. Cool! <br/><br/>";
    ?>
    
    <?php
    if ( !file_exists($config) ) {
        echo "Uh-oh, we already ran into a problem. I can't find a config file! 
        I'm looking for $config";
        exit;
    }

    $config      = PROJECT_ROOT.'/config/config.xml';
    $app_config  = PROJECT_ROOT.'/apps/'.APP_NAME.'/config/config.xml';
    $config_file = file_get_contents($config);

    if (!isset($mode)) {
        $mode = "dev";
    }
    if (isset($_ENV['NEXISTA_MODE'])) {
        $mode = $_ENV['NEXISTA_MODE'];
    }
    if (!file_exists($app_config)) {
        $foundry->configure($config, null, $mode);
    } else {
        $foundry->configure($config, $app_config, $mode);
    }

    $foundry->debug = 1;

    $my_sitemap = $foundry->getSitemapPath('./build/sitemap');

    $foundry->buildLoader();
    $foundry->buildGates();
    $foundry->buildSitemap();
    ?>

    <script type="text/javascript">
    setTimeout('top.location.reload()',1000);
    </script>
    <?php
    ob_flush();
}
