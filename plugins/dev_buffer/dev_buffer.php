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
    $development_console = true;
    $excludes = Nexista_Config::get('./plugins/dev_buffer/excludes');
    if(strpos($excludes,',')) { 
        $x_array = explode(',',$excludes);
    } else { 
        $x_array[] = $excludes;
    }

    if(in_array($_GET['nid'],$x_array)) {
        unset($development_console);
    }
    if($development_console===true) {
        development_console();
    }

	$output = $init->run();

    if($development_console===true) { 
        final_notices($cache_type,"dev");
    }
	if(isset($_GET['view_flow'])){
        if($_GET['view_flow']=="true"){
            view_flow();
        }
	}

	ob_end_flush();
    echo $output;
	header("Content-Length: ".ob_get_length());
	ob_end_flush();
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


