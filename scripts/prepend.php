<?php

/* 

In development you will never do client side caching, but on every stage you
will do server side caching. 

*/

if((bool)Config::get('./runtime/cache')===true){
    if(count($_POST) > 0 || $_GET['from_date'] || $_GET['nid']=="logout") { 
        $gate_cache_file = NX_PATH_CACHE.'cache_*';
        
        foreach (glob("$gate_cache_file") as $filename) {
           unlink($filename);
        }
        
    }
}


if(COMMAND_LINE===true) {
} else {
Init::registerOutputHandler('gzBuffer');
}


function gzBuffer($init)
{
	$init->process();
	
	$request_uri = $_SERVER['REQUEST_URI'];
	Flow::add("request_uri",$request_uri);
    
	ob_start();
	ob_start('ob_gzhandler');
	/*
    // You could set an environmental variable from the webserver
    // to identify user or user agent
	$agent = $_SERVER['HTTP_USER_AGENT'];
	if(stripos($agent,"google") || stripos($agent,"slurp") || stripos($agent,"msnbot") || stripos($agent,"spider") || stripos($agent,"bot")) { 		
		$my_user_id = -1000;
	} else { 
		//$my_user_id = $_SESSION['NX_AUTH']['user_id'];
		$my_user_id = 100;
	}
    */

	$expiryTime=$init->getInfo('cacheExpiryTime');
	$my_request_uri = $request_uri;
	$clear_gate_file='cache_'.$my_user_id."_".$my_request_uri;
	if(!include('Cache/Lite.php')) { 
        echo "Error: Can't find PEAR package cache_lite";
        exit;   
    }
    if(!is_dir(NX_PATH_CACHE)) { 
        mkdir(NX_PATH_CACHE);
    }
        
    $cache_config = Config::get('./runtime/cache');
	$options = array('cacheDir'=> NX_PATH_CACHE,'caching'  => $cache_config,'lifeTime' => $expiryTime);
	$cache = new Cache_Lite($options);

    $development_console = (bool)Config::get('./runtime/development_console');
    if($_GET['development_console']=="false") { 
        unset($development_console); 
    }
    if($development_console===true) { 
        development_console();
    }
	if(isset($_SESSION['NX_AUTH']['real_account_id']) && $file_server_status!="yes") { 
		cs_console();
	}
    
    
	// Server cache! Always on, controlled by sitemap.
	// Server cache especially helpful for ssl connections.
	if($output = $cache->get($my_request_uri, $my_user_id, TRUE)) { 
		$cache_type = "File";
		// Check file mtime, and potentially return 304 response code.
		// Only for testing and production sites.
		$mynid = NX_PATH_CACHE.'cache_'.md5($my_user_id).'_'.md5($my_request_uri); 		
		$last_modified_str = filemtime($mynid);
		$file_size = filesize($mynid);
		$etag = md5($last_modified_str);
		$last_modified = gmdate('D, d M Y H:i:s', $last_modified_str);
		
        // Where is the modified since header set? In the browser, nowhere else
        // Apache sets an expires header, which allows the browser 
        // to use the cache without checking the server to ask if anything 
        // has been modified
        // Should we set it here?
        if(function_exists(getallheaders)) {  
            $request = getallheaders(); // Apache Module Only
            if (isset($request['If-Modified-Since'])) { 
               $modifiedSince = explode(';', $request['If-Modified-Since']); 
               $modifiedSince = strtotime($modifiedSince[0]); 
            } else { 
               $modifiedSince = 0; 
            }
        } else { 
            // CGI
            if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) { 
               $modifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE']; 
               $modifiedSince = strtotime($modifiedSince); 
            } else { 
                $modifiedSince = 0;
            }
        }
        
		// Client cache!
        $client_cache = $init->getInfo('clientCacheExpiryTime');
        // Should we calculate the time? Sure... need to make sure the 
        // modified time plus the client cache is greater than the 
        // if modified since time...
        $lms = $modifiedSince;
        $client_cache_work = 
            mktime(date('H',$lms), date('i',$lms), date('s',$lms)+$client_cache, 
                    date('m',$lms), date('d',$lms), date('Y',$lms));
        $client_cache_good_stamp = strtotime($client_cache_work);
        if($client_cache > 0 && $client_cache_work > time()) {
            while (@ob_end_clean());
            header( 'Cache-Control: no-cache, must-revalidate, post-check='.$client_cache.', pre-check='.$client_cache);
            header("HTTP/1.0 304 Not Modified");
            exit();
        }
        
        // When using client cache a session cache limiter, you've got to use this cache-control
        // header.
        header( 'Cache-Control: no-cache, must-revalidate, post-check=3600, pre-check=3600');
		header("Last-Modified: " . $last_modified . " GMT");
	
	} else { 
        header( 'Cache-Control: no-cache, must-revalidate, post-check=3600, pre-check=3600');
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		$output = $init->run();
		/* Write the output to cache, only if its not encrypted data. */
		$encrypt = Path::get("//get_wiki_page/encrypt", "flow"); 
		if($expiryTime > 2 && $encrypt!="1" && $_GET['view_flow']!=true) { 
			$cache->save($output, $my_request_uri, $my_user_id);
		}
		$cache_type = "None";
	}	
	if(isset($_GET['view_flow'])){
        if($_GET['view_flow']=="true"){
            view_flow();
        }
	}
	if(isset($etag)) { 
        header("ETag: ".$etag);
    }
	echo $output;
    if($development_console === true) {
        final_notices($cache_type,"dev");
    }
	
	ob_end_flush();
	
	
	header("Content-Length: ".ob_get_length());
	ob_end_flush();
	
	
	
	
	
}
 








/* This function used by all stages. */
function cs_console()  {
	$blah = new XsltProcessor();
	$xsl = new DomDocument;
	$xsl->load(NX_PATH_APPS."_shared/xsl/impersonate_header.xsl");
	$blah->importStyleSheet($xsl);
	$flow = Flow::singleton();
	$user_name=$_SESSION['NX_AUTH']['user_name'];
	Flow::add("user_name",$user_name);
	echo $blah->transformToXML($flow->flowDocument);
}


/* This function only used on development stage. */
function development_console()  {

	$blah = new XsltProcessor();
	$xsl = new DomDocument;
	$xsl->load(NX_PATH_CORE."xsl/dev_prepend.xsl");
	$blah->importStyleSheet($xsl);
	$flow = Flow::singleton();
	$request_uri = $_SERVER['REQUEST_URI'];
	Flow::add("request_uri",$request_uri);
	echo $blah->transformToXML($flow->flowDocument);

}
 
function view_flow() { 
	$debugXsl = new XsltProcessor();
	$xsl = new DomDocument;
	$xsl->load(NX_PATH_CORE."xsl/flow.xsl");
	$debugXsl->importStyleSheet($xsl);
	$flow = Flow::singleton();
	echo $debugXsl->transformToXML($flow->flowDocument);
}


/* This function used on dev and test development stages. */
function final_notices($cacher=null, $mode) { 
	$my_total_time = Debug::profile();
	$final_notices =  "<div width='100%' 
    style='background: #e3b6ec; padding: 3px; position: absolute; top: 0px; right: 0px;'>
		Elapsed Server Time: $my_total_time , Elapsed Client Time:  
<script type='text/javascript'>

done_loading();</script> - Server cache: $cacher <!--[ <a href='/acc/cache/purge/'>Purge</a> ]--> </div>";
	echo $final_notices;
}


function authLogin($auth)
{
    if(COMMAND_LINE===true) {
    } else {
        if(empty($_SESSION['authReferer']))
        {
            $_SESSION['authReferer'] = $_SERVER['REQUEST_URI'];
        }
        $link_prefix = dirname(NX_LINK_PREFIX);
        header("Location: ".$link_prefix."/auth.php?nid=login");
        exit;
    }
}

Auth::registerTimeoutHandler('authLogin');
Auth::registerLoginHandler('authLogin');
Auth::registerDeniedHandler('authLogin');
Auth::registerExpiredHandler('authLogin');
