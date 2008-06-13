<?php

/**
 * Extension Name: Caching Output
 * Extension URI:
 * Description: Uses Cache_Lite to compress and cache output.
 * Version: 0.1
 * Copyright: Nexista
 * Author: Albert Lash
 * License: LGPL
 * Status: beta
 *
 * PHP version 5
 *
 * @category Nexista_Extensions
 * @package  Nexista_Cache
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
    <!-- refresh divisor:
    a value of 10 would touch a still fresh cache with less than
    1/10th of the cache lifetime, making if fresh again.
    since it has a new modification time, it will be refreshed by the
    client, but it won't be invalidated.
    -->
    <refresh_divisor></refresh_divisor>
    <timers>1</timers>
    <excludes></excludes>
    <purge_gates>list-of-gates,logout</purge_gates>
    <purge_gets>from_date</purge_gets>
    <gc_divisor></gc_divisor>
    <absmaxlife></absmaxlife>
</nexista_cache>
*/


$active = (bool)Nexista_Config::get("./extensions/nexista_cache/active");

if($active) {
    /* PURGE CACHE */
    if ( // POST will always purge the cache
        count($_POST) > 0 ||
        // need a foreach loop here
        $_GET['from_date'] ||
        // and here
        $_GET['nid']=="logout" ) {
        $gate_cache_file = NX_PATH_CACHE.'cache_*';

        foreach (glob("$gate_cache_file") as $cac) {
            unlink($cac);
        }

    } else {
        /* Garbage Collection */
        /* TODO - Finish this */
        if (rand(0,100) < 10) {
            // Do GC
            $gate_cache_file = NX_PATH_CACHE.'cache_*';

            foreach (glob("$gate_cache_file") as $cac) {
                $lm  = filemtime($cac);

                if (!$absmaxlife) 
                    $absmaxlife = 3600;

                if (time() > $lm + $absmaxlife) {
                    unlink($cac);
                }
            }
        }
        // Note - Cache_Lite has automatic cleaning - does it work?
        // Sounds good:
        /*
        * Disable / Tune the automatic cleaning process
        *
        * The automatic cleaning process destroy too old (for the given life time)
        * cache files when a new cache file is written.
        * 0               => no automatic cache cleaning
        * 1               => systematic cache cleaning
        * x (integer) > 1 => automatic cleaning randomly 1 times on x cache write
        *
        */
    }
}

/* Handle Exclusions */
$included = true;
$excludes = Nexista_Config::get('./extensions/nexista_cache/excludes');
if(strpos($excludes,',')) {
    $x_array = explode(',',$excludes);
} else {
    if(!empty($excludes)) {
        $x_array[] = $excludes;
    }
}

if(!empty($x_array)) {
    if(in_array($_GET['nid'],$x_array)) {
        unset($included);
    } else {
        // this could be slow, might want to have a setting to turn on / off
        foreach($x_array as $value) {
            if(eregi($value,$_GET['nid'])) {
                unset($included);
            }
        }
    }
}
/* END EXCLUSIONS */


if ($included && $active) {
    Nexista_Init::registerOutputHandler('Nexista_cache');
}






/**
 * nexista_cache
 *
 * Output buffer utilizing PEAR_Cache
 *
 * @param object $init includes stuff
 *
 * @return boolean
 */
function Nexista_cache($init)
{

    //configuration - move to xml
    $timers = true;

    //necessary stuff no matter what
    $init->process();
    $uid = Nexista_Flow::get('//runtime/user_id');
    $uri = $_SERVER['REQUEST_URI'];
    $cac = NX_PATH_CACHE . 'cache_' . md5($uid) . '_' . md5($uri);
    $exp = $init->getInfo('cacheExpiryTime');


    //first things first - check for if-modified-since
    if ($ims = $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
        if (is_file($cac)) {
            $ims  = strtotime($ims);
            $lm   = filemtime($cac);
            $etag = md5(file_get_contents($cac));
            $ctag = $_SERVER['HTTP_IF_NONE_MATCH'];
            if ($lm <= $ims && $etag == $ctag) {
                // Hasn't been modified, is it still fresh?
                $fresh = $lm + $exp - time();
                if( $fresh > 0 ) {
                    header("HTTP/1.1 304 Not Modified");
                    // If its close to expiring, extend life
                    if( $fresh < $exp / 10 ) {
                        touch($cac);
                    }
                    exit;
                }
                // if not fresh, it will be rebuilt
            }
        }
    }


    include_once 'Cache/Lite.php';
    /* TODO - Add autocleaning */
    $options = array(
            'cacheDir' => NX_PATH_CACHE,
            'caching'  => true,
            'automaticCleaningFactor' => 0,
            'readControl'  => false, // necessary to send etag
            'lifeTime' => $exp
            );

    $cache = new Cache_Lite($options);

    $content_type = $init->getInfo('content_type');
    $cache_control = $init->getInfo('cache_control');

    ob_start();
    ob_start();

    if ($output = $cache->get($uri, $uid)) {
        $cache_type = "file cache";
        $lm = filemtime($cac);
        header("Last-Modified: " . gmdate('D, d M Y H:i:s', $lm) . " GMT");

    } else {

        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        $cache_type = "no cache";

        $output = $init->run();

        if ($timers) {
            $server_time = Nexista_Debug::profile();

            if ( $content_type == "text/html" ) {
                $output = str_replace("</body>", "", $output);
                $output = str_replace("</html>", "", $output);

                $output .= "\n\n<!--\nOriginal request required: $server_time! \n-->\n\n";
                $output .= "</body></html>";
            } elseif ( $content_type == "text/css" ) {
                $output .=  "\n/* Original request required: $server_time! */";
            }
        }

        if (!is_dir(NX_PATH_CACHE))
            @mkdir(NX_PATH_CACHE, 0700, true);

        $cache->save($output, $uri, $uid);
        header("ETag: ".md5($output));
    }

    if ( $content_type == "text/html" ) {
        $output = str_replace("</body>", "", $output);
        $output = str_replace("</html>", "", $output);
    }

    echo $output;
    if ($timers) {
        $server_time = Nexista_Debug::profile();

        if ( $content_type == "text/html" ) {
            echo "<!--\n";
            echo "Nexista Cache Information:\n";
            echo "Output generated by $cache_type in $server_time.\n";
            echo "Output sent with Cache-Control: $cache_control headers, which will affect future requests.\n";
            if ($lm > 0) {
                echo " Last modified:  $lm. ";
                echo "Cache created: ". gmdate('D, d M Y H:i:s', $lm) . " GMT\n";
            }
            echo "Current time: " . gmdate('D, d M Y H:i:s') . " GMT\n-->\n";
            echo "</body></html>";
        } elseif ( $content_type == "text/css" ) {
            echo "\n/* Output generated by $cache_type in $server_time. Last modified:  $lm. */";
        } elseif ( $content_type == "text/x-javascript" ) {
            echo "\n// Output generated by $cache_type in $server_time. Last modified:  $lm. ";
        }
    }
    ob_end_flush();
    header("Content-Length: ".ob_get_length());
    ob_end_flush();

}
