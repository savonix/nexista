<?php
/*
Plugin Name: Compressing, Caching Output
Plugin URI:
Description: Uses ob_gzhandler and Cache_Lite to compress and cache output.
Version: 0.1
Copyright: Savonix Corporation
Author: Albert Lash
License: LGPL
Status: beta
*/

if(count($_POST) > 0 || $_GET['from_date'] || $_GET['nid']=="logout") {
    $gate_cache_file = NX_PATH_CACHE.'cache_*';

    foreach (glob("$gate_cache_file") as $filename) {
       unlink($filename);
    }
}

Nexista_Init::registerOutputHandler('nexista_cache');

function nexista_cache($init)
{
    // Should probably have a check against apache and config.xml to
    // see if this will work.
    $init->process();

    if(!is_dir(NX_PATH_CACHE)) {
        mkdir(NX_PATH_CACHE);
    }
    require_once 'Cache/Lite.php';

	ob_start();
    ob_start();
	$my_request_uri = $_SERVER['REQUEST_URI'];
    // Server cache
	$expiryTime = $init->getInfo('cacheExpiryTime');
    // Client cache!
    $client_cache = $init->getInfo('clientCacheExpiryTime');

	$clear_gate_file='cache_'.$my_user_id."_".$my_request_uri;
    $active = Nexista_Config::get("./plugins/nx_cache/active");
    if($expiryTime == '0') {
        $active = 0;
    }
	$options = array('cacheDir'=> NX_PATH_CACHE,'caching'  => $active,'lifeTime' => $expiryTime);
	$cache = new Cache_Lite($options);

	// Server cache
	if($output = $cache->get($my_request_uri, $my_user_id, TRUE)) {
        $mynid = NX_PATH_CACHE.'cache_'.md5($my_user_id).'_'.md5($my_request_uri);
        $last_modified_str = filemtime($mynid);
		$last_modified = gmdate('D, d M Y H:i:s', $last_modified_str);

        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
           $lms = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
           $lms = strtotime($lms);
        } else {
            $lms = 0;
        }

        $client_cache_work =
            gmmktime(gmdate('H',$lms), gmdate('i',$lms), gmdate('s',$lms)+$client_cache,
                    gmdate('m',$lms), gmdate('d',$lms), gmdate('Y',$lms));
        $client_cache_good_stamp = strtotime($client_cache_work);
        if($client_cache > 0 && $client_cache_work > time('UTC')) {
            while (@ob_end_clean());
            header( 'Cache-Control: no-cache, must-revalidate, post-check='.$client_cache.', pre-check='.$client_cache);
            header("HTTP/1.1 304 Not Modified");
            exit;
        } elseif($client_cache > 0 && $client_cache_work < time('UTC')) {
            unlink($mynid);
            if($client_cache > 0) {
            $client_cache_work =
                gmdate('D, d M Y H:i:s', mktime(date('H'), date('i'), date('s')+$client_cache,
                        date('m'), date('d'), date('Y')));
            } else {
                header("Cache-Control: no-cache, must-revalidate, post-check=3600, pre-check=3600");
            }
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            $output = $init->run();
            $cache->save($output, $my_request_uri, $my_user_id);
        } else {
            // When using client cache and a session cache limiter, you've got to use this cache-control
            // header.
            header("Cache-Control: no-cache, must-revalidate, post-check=3600, pre-check=3600");
            header("Last-Modified: " . $last_modified . " GMT");
        }
	} else {
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		$output = $init->run();
        $cache->save($output, $my_request_uri, $my_user_id);
	}

	echo $output;
	ob_end_flush();
	header("Content-Length: ".ob_get_length());
	ob_end_flush();

}