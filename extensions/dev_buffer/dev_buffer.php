<?php
/*
Plugin Name: Development Output Buffer
Plugin URI:
Description:
Version: 0.1
Copyright: Savonix Corporation
Author: Albert Lash
License: LGPL
*/

/* TODO:
    1. Add a hook to nexista_cache for debugging purposes.
    2. Add custom error handling for more detailed error reports when developing.
    3. Allow config.xml to add sitemaps to the main one for setting up the gates
        needed to support dev_buffer.
*/


/*
This section is always processed. It checks for exlusions to the
development output buffer.
*/
$development_console = true;
$excludes = Nexista_Config::get('./extensions/dev_buffer/excludes');
if(strpos($excludes,',')) {
    $x_array = explode(',',$excludes);
} else {
    if(!empty($excludes)) {
        $x_array[] = $excludes;
    }
}

if(!empty($x_array)) {
    if(in_array($_GET['nid'],$x_array)) {
        unset($development_console);
    } else {
        // this could be slow, might want to have a setting to turn on / off
        foreach($x_array as $value) {
            if(eregi($value,$_GET['nid'])) {
                unset($development_console);
            }
        }
    }
}

if($development_console===true) {
    Nexista_Init::registerOutputHandler('nexista_devBuffer');
}


function nexista_devBuffer($init)
{
	$init->process();

	ob_start();
    ob_start();

    header( 'Cache-Control: no-cache, must-revalidate, post-check=3600, pre-check=3600');
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

    nexista_development_console();

	$output = $init->run();


	if(isset($_GET['view_flow'])){
        if($_GET['view_flow']=="true"){
            nexista_view_flow();
        }
	}

    $output = str_replace("</body>","",$output);
    $output = str_replace("</html>","",$output);
    echo $output;
    echo nexista_final_notices($cache_type,"dev");
    echo "</body></html>";


	ob_end_flush();
	header("Content-Length: ".ob_get_length());
	ob_end_flush();
}


function nexista_development_console()  {

$my_script = <<<EOL
	<script type="text/javascript">
	var began_loading = (new Date()).getTime();
	function done_loading(server_total) {
		document.getElementById('server_time').firstChild.nodeValue = server_total + ' s';
	}
	function done_loading_js() {
		var total = (((new Date()).getTime() - began_loading)) / 1000;
		document.getElementById('client_time').firstChild.nodeValue = total + ' s';
	}
	</script>
EOL;

$in_head[] = array('string' => $my_script, 'priority' => 10);
Nexista_Flow::add("in_head",$in_head,false);

$my_uri = $_SERVER['REQUEST_URI'];
// For AJAX-based flow dump, these calls would need to be made into javascript functions
// I'd like to use sarissa to do the transforms, though an iframe might work too.
if(strpos($my_uri,"&view_flow=true")) {
    $my_button = '[ <a href="'.str_replace("&view_flow=true","",$my_uri).'">Hide Flow</a> ]';
} elseif(strpos($my_uri,"view_flow=true&")) {
    $my_button = '[ <a href="'.str_replace("view_flow=true&","",$my_uri).'">Hide Flow</a> ]';
} else {
    $my_button = '[ <a href="'.$my_uri.'&view_flow=true">View Flow</a> ]';
}
$my_cache_purge = '[ <a href="#" onclick="$.post(\''.$my_uri.'\', { purge: \'true\' }, function(data){
  document.getElementById(\'purger\').firstChild.nodeValue = \'Done\';
});">Purge Cache</a> ]';
$admin_panel = <<<EOL
<div style="padding-bottom: 10px;  font-family: mono;">
<table width="100%" cellpadding="2" style="background-color: #e3b6ec;">
<tr><td style="background-color: #e3b6ec;">
		$my_button
        Server time:<span id="server_time"> 0.000 s </span>
        Client time:<span id="client_time"> 0.000 s </span>
    </td>
    <td style="background-color: #e3b6ec;">$my_cache_purge &#160;<span id="purger" style="color: red;">&#160;&#160;&#160;&#160;</span></td>
	</tr></table></div>
EOL;
$pre_body_content[] = array('string' => $admin_panel, 'priority' => 10);
Nexista_Flow::add("pre_body_content",$pre_body_content,false);
}

function nexista_view_flow() {
	$debugXsl = new XsltProcessor();
	$xsl = new DomDocument;
	$xsl->load(NX_PATH_BASE."extensions/dev_buffer/flow.xsl");
	$debugXsl->importStyleSheet($xsl);
    if(isset($_GET['ignore'])) {
        $debugXsl->setParameter('','ignore',$_GET['ignore']);
    } else {
        $debugXsl->setParameter('','ignore','i18n');
	}
    $debugXsl->setParameter('','link_prefix',dirname($_SERVER['SCRIPT_NAME']).'/index.php?nid=');
    $flow = Nexista_Flow::singleton();
	echo $debugXsl->transformToXML($flow->flowDocument);
    
    /*
    // Used along, this will provide a flow dump by itself.
    header("Content-type: text/xml");
    $xout = $flow->flowDocument->saveXML();
    $xout = str_replace('<?xml version="1.0"?>','<?xml version="1.0"?><?xml-stylesheet type="text/xsl" href="index.php?nid=--flowxsl--dev"?>',$xout);
    echo $xout;
    exit;
    */
}


/* This function outputs a small script used to pass the final processing time,
and to stop the client timer.. */
function nexista_final_notices($cacher=null, $mode) {
	$server_time = Nexista_Debug::profile();
$final_notices =  "
<script type='text/javascript'>
    done_loading($server_time);
    if (typeof jQuery != 'undefined') {
        $(document).ready(function()
        {
            done_loading_js();
        });
    }
</script>\n";
    return $final_notices;
    
}
