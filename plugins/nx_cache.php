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

Nexista_Init::registerOutputHandler('nx_cache');

function nx_cache($init)
{

    if(!is_dir(NX_PATH_CACHE)) { 
        mkdir(NX_PATH_CACHE);
    }

	$init->process();

	ob_start();
	$my_request_uri = $_SERVER['REQUEST_URI'];
	$expiryTime=$init->getInfo('cacheExpiryTime');
	$clear_gate_file='cache_'.$my_user_id."_".$my_request_uri;

	$options = array('cacheDir'=> NX_PATH_CACHE,'caching'  => $cache_config,'lifeTime' => $expiryTime);
	$cache = new Cache_Lite($options);

	// Server cache! Always on, controlled by sitemap.
	if($output = $cache->get($my_request_uri, $my_user_id, TRUE)) { 
		ob_start();
        $mynid = NX_PATH_CACHE.'cache_'.md5($my_user_id).'_'.md5($my_request_uri); 		
		$last_modified_str = filemtime($mynid);
		$etag = md5($last_modified_str);
		$last_modified = gmdate('D, d M Y H:i:s', $last_modified_str);
		
        // Where is the modified since header set? In the browser, nowhere else
        // Apache sets an expires header, which allows the browser 
        // to use the cache without checking the server to ask if anything 
        // has been modified
        // Should we set it here?
        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) { 
           $modifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE']; 
           $modifiedSince = strtotime($modifiedSince); 
        } else { 
            $modifiedSince = 0;
        }
        
		// Client cache!
        $client_cache = $init->getInfo('clientCacheExpiryTime');
        $lms = $modifiedSince;
        $client_cache_work = 
            mktime(date('H',$lms), date('i',$lms), date('s',$lms)+$client_cache, 
                    date('m',$lms), date('d',$lms), date('Y',$lms));
        $client_cache_good_stamp = strtotime($client_cache_work);
        if($client_cache > 0 && $client_cache_work > time()) {
            while (@ob_end_clean());
            header( 'Cache-Control: no-cache, must-revalidate, post-check='.$client_cache.', pre-check='.$client_cache);
            header("HTTP/1.1 304 Not Modified");
            exit();
        }
        
        // When using client cache a session cache limiter, you've got to use this cache-control
        // header.
        header("Cache-Control: no-cache, must-revalidate, post-check=3600, pre-check=3600");
		header("Last-Modified: " . $last_modified . " GMT");
	
	} else { 
        if(!(bool)Nexista_Config::get('./plugins/nx_cache/compress')) { 
            ob_start();
        } else { 
            ob_start('ob_gzhandler');
        }

        header("Cache-Control: no-cache, must-revalidate, post-check=3600, pre-check=3600");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		$output = $init->run();
        $cache->save($output, $my_request_uri, $my_user_id);
		$cache_type = "None";
	}
	if(isset($etag)) { 
        header("ETag: ".$etag);
    }
	echo $output;
	ob_end_flush();
	header("Content-Length: ".ob_get_length());
	ob_end_flush();
	

}
