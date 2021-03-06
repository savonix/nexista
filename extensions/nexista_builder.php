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

function Nexista_Check_freshness($server_init)
{
    global $app_config;
    global $foundry;
    global $server_init;

    $config      = PROJECT_ROOT.'/config/config.xml';
    $app_config  = PROJECT_ROOT.'/apps/'.APP_NAME.'/config/config.xml';

    // Should sitemap be checked? not usually.
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

        nexista_build_it_now($server_init);

    }
}

function Nexista_Build_It_now($server_init)
{
    header('Content-type: text/html');

    if (!file_exists(NX_PATH_BASE.'kernel/foundry.php')) {
        echo 'I am unable to find the nexista foundry class, and cannot continue. 
        Try this:
        <br/><br/><a href="http://www.nexista.org">http://www.nexista.org</a>, 
        and so you know, I looking here: <br/>';
        echo NX_PATH_BASE.'kernel/foundry.php';
        exit;
    } else {
        require NX_PATH_BASE.'kernel/foundry.php';
    }

    $foundry = Nexista_Foundry::singleton('Nexista_Foundry');

    $config     = PROJECT_ROOT.'/config/config.xml';
    $app_config = PROJECT_ROOT.'/apps/'.APP_NAME.'/config/config.xml';

    ob_end_clean();
    header('Cache-Control: no-cache, no-store');
    ?>

    <html>
    <body style="padding: 150px; font-family: verdana;">
    <?php
    echo 'Looks like you are installing to ',$server_init, ' Cool! <br/><br/>';
    ?>
    
    <?php
    if ( !file_exists($config) ) {
        echo 'Uh-oh, we already ran into a problem. Where is the config file? 
        It is not here: ',$config;
        echo '<br/><br/>**To proceed, config/config.xml.dist must be copied to 
        config/config.xml and configured.**';
        exit;
    }

    $config_file = file_get_contents($config);

    if (!isset($mode)) {
        $mode = 'dev';
    }
    if (isset($_ENV['NEXISTA_MODE'])) {
        $mode = $_ENV['NEXISTA_MODE'];
    }

    if (!file_exists($app_config)) {
        if(!$foundry->configure($config, null, $mode)) {
            echo '<br/><br/>**
            Something is amiss. Please check to make sure the cache
            directory specified in config.xml is writeable by the webserver.**';
            exit;
        }
    } else {
        if(!$foundry->configure($config, $app_config, $mode)) {
            echo '<br/><br/>**
            Something is amiss. Please check to make sure the cache
            directory specified in config.xml is writeable by the webserver.**';
            exit;
        }
    }

    $foundry->debug = 1;

    $my_sitemap = $foundry->getSitemapPath('./build/sitemap');

    $foundry->buildLoader();
    $foundry->buildGates();
    $foundry->buildSitemap();
    ?>

<script type="text/javascript">
if(done_building=="true") {
    setTimeout('top.location.reload()',1500);
}
</script>
</body></html>
    <?php
    ob_flush();
    exit;
}
