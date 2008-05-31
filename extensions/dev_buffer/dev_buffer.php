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
    4. Don't use $_GET['nid'], use the flow equivalent
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
$mylink = $_SERVER['SCRIPT_NAME'];
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
// AJAX
if(strpos($my_uri,"&client_view_flow=true")) {
    $my_button = '[ <a href="'.str_replace("&client_view_flow=true","",$my_uri).'">Hide Flow</a> ]';
} elseif(strpos($my_uri,"client_view_flow=true&")) {
    $my_button = '[ <a href="'.str_replace("client_view_flow=true&","",$my_uri).'">Hide Flow</a> ]';
} else {
    $my_button = '[ <a href="'.$my_uri.'&client_view_flow=true">View Flow</a> ]';
}

// This button will rebuild the application, as well as purge the cache
$rebuild_button = '[ <span style="cursor: pointer;" onclick="$.post(\''.$mylink.'\', { \'x--dev--rebuild\': \'true\' }, function(data){
  document.getElementById(\'builder\').firstChild.nodeValue = \'Done\';
});">Rebuild</span> ]';

// This button will only purge the cache
$my_cache_purge = '[ <span style="cursor: pointer;" onclick="$.post(\''.$mylink.'\', { \'x--dev--purge\': \'true\' }, function(data){
  document.getElementById(\'purger\').firstChild.nodeValue = \'Done\';
});">Purge Cache</span> ]';


if($_GET['client_view_flow']=="true") { 
$flow_button = <<<EOL
[ <span id="flowDump-button" onclick="divExpand('flowDumpContent', true)" title="Click to expand/contract">
Toggle Flow</span> ]
EOL;
}
$admin_panel = <<<EOL
<table width="100%" cellpadding="2" style="background-color: #e3b6ec;">
<tr><td style="background-color: #e3b6ec;" width="50%">
		$my_button $rebuild_button <span id="builder" style="color: red;">&#160;&#160;&#160;&#160;</span>
        Server time:<span id="server_time"> 0.000 s </span>
        Client time:<span id="client_time"> 0.000 s </span>
    </td>
    <td width="25%" style="background-color: #e3b6ec;">$my_cache_purge &#160;<span id="purger" style="color: red;">&#160;&#160;&#160;&#160;</span></td>
	<td width="25%">
    $flow_button
    </td></tr></table>

EOL;



if($_GET['client_view_flow']=="true") {

$mynid = $_GET['nid'];
$flow_viewport = <<<EOL
<script type="text/javascript">
$(document).ready( function(){
    $('#flow_viewport').getTransform(
        '$mylink?nid=x--dev--flow.xsl',
        '$mylink?nid=$mynid&view_flow=true&flowxml=true',
        {
            params: {
                'ignore': 'i18n'
            },
            xpath: '/',
            eval: false,
            callback: function(){
                $('#flow_viewport').css({"visibility":"visible"});
            }
        }
    );
});
</script>
<div id="flow_viewport" style="display: block; visibility: hidden;">
<br/>
</div>
EOL;

$pre_body_content[] = array('string' => $flow_viewport, 'priority' => 11);

$head_includes = <<<EOL
<script type="text/javascript" src="$mylink?nid=x--dev--sarissa.js"></script>
<script type="text/javascript" src="$mylink?nid=x--dev--sarissa_ieemu_xpath.js"></script>
<script type="text/javascript" src="$mylink?nid=x--dev--jquery.n.friends.js"></script>

EOL;
$head_content[] = array('string' => $head_includes, 'priority' => 10);
Nexista_Flow::add("in_head",$head_content,false);

}
$pre_body_content[] = array('string' => $admin_panel, 'priority' => 10);

Nexista_Flow::add("pre_body_content",$pre_body_content,false);

}



function nexista_view_flow() {
    $flow = Nexista_Flow::singleton();
	if($_GET['flowxml']=="true") {
        // XML output
        header("Content-type: text/xml");
        if($_GET['full']==true){
        } else {
            // TODO - Make this configurable
            $exclude = $flow->flowDocument->documentElement;
            $removeme = $exclude->getElementsByTagName('i18n')->item(0);
            $removeme->parentNode->removeChild($removeme);
        }
        //$flow->flowDocument->normalizeDocument();
        $xout = $flow->flowDocument->saveXML();
        echo $xout;
        exit;
    } else {
        // Transform into HTML form
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
        echo $debugXsl->transformToXML($flow->flowDocument);
    }

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
