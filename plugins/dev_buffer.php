<?php
/*
Plugin Name: Development Output Buffer
Plugin URI: 
Description: 
Version: 0.1
Copyright: Savonix Corporation
Author: Albert Lash
License: GPL v3 or later
*/


Nexista_Init::registerOutputHandler('devBuffer');

function devBuffer($init)
{
	$init->process();

	ob_start();
	ob_start();

    if($_GET['development_console']=="false") { 
        unset($development_console); 
    }
    if($development_console===true) { 
        development_console();
    }
	if(isset($_SESSION['NX_AUTH']['real_account_id']) && $file_server_status!="yes") { 
		cs_console();
	}
    
    

	$output = $init->run();

    if($development_console === true) {
        final_notices($cache_type,"dev");
    }
	if(isset($_GET['view_flow'])){
        if($_GET['view_flow']=="true"){
            view_flow();
        }
	}
	
	ob_end_flush();
	
	
	header("Content-Length: ".ob_get_length());
    echo $output;
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
	Nexista_Flow::add("user_name",$user_name);
	echo $blah->transformToXML($flow->flowDocument);
}


/* This function only used on development stage. */
function development_console()  {

	$blah = new XsltProcessor();
	$xsl = new DomDocument;
	$xsl->load(NX_PATH_CORE."xsl/dev_prepend.xsl");
	$blah->importStyleSheet($xsl);
	$flow = Nexista_Flow::singleton();
	$request_uri = $_SERVER['REQUEST_URI'];
	Nexista_Flow::add("request_uri",$request_uri);
	echo $blah->transformToXML($flow->flowDocument);

}
 
function view_flow() { 
	$debugXsl = new XsltProcessor();
	$xsl = new DomDocument;
	$xsl->load(NX_PATH_CORE."xsl/flow.xsl");
	$debugXsl->importStyleSheet($xsl);
    if(isset($_GET['ignore'])) { 
        $debugXsl->setParameter('','ignore',$_GET['ignore']);
    } else { 
        $debugXsl->setParameter('','ignore','i18n');
	}
    $flow = Nexista_Flow::singleton();
	echo $debugXsl->transformToXML($flow->flowDocument);
}


/* This function used on dev and test development stages. */
function final_notices($cacher=null, $mode) { 
	$my_total_time = Nexista_Debug::profile();
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

Nexista_Auth::registerTimeoutHandler('authLogin');
Nexista_Auth::registerLoginHandler('authLogin');
Nexista_Auth::registerDeniedHandler('authLogin');
Nexista_Auth::registerExpiredHandler('authLogin');
