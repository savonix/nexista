<?php
/* <!--
Plugin Name: Development Output Buffer
Plugin URI:
Description:
Version: 0.1
Copyright: Nexista
Author: Albert Lash
License: LGPL

TODO:
    1. Add a hook to nexista_cache for debugging purposes.
    2. Add custom error handling for more detailed error reports when developing.
    3. Allow config.xml to add sitemaps to the main one for setting up the gates
        needed to support dev_buffer.
    4. Don't use $_GET['nid'], use the flow equivalent
--> */


/* START EXCLUSIONS CHECK */
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

/* END EXCLUSIONS */



if($development_console===true) {
    Nexista_Init::registerOutputHandler('nexista_devBuffer');
}
if (!function_exists('nexista_buildererror')) {
    Nexista_Error::addObserver('display', 'Nexista_builderError');

    /**
     * Error...
     *
     * @param object $e error object
     *
     * @return null
     */

    function Nexista_builderError($e)
    {
        if ($e->getCode() == NX_ERROR_FATAL ||
            $e->getCode() == NX_ERROR_WARNING
            ) {
            $use_xslt_cache = "yes";
            if ($use_xslt_cache!="yes" || !class_exists('xsltCache')) {
                $exceptionXsl = new XsltProcessor();
            } else {
                $exceptionXsl = new xsltCache;
            }
            $xsl = new DomDocument;
            $my_xsl_file = NX_PATH_BASE.'extensions/dev_buffer/s/xsl/exception.xsl';
            if (file_exists($my_xsl_file)) {
                $xsl->load($my_xsl_file);
                $exceptionXsl->importStyleSheet($xsl);
                $xml = new DomDocument;
                $xml->loadXML($e->outputXml());
                $exceptionXsl->setParameter('',
                    'link_prefix', dirname($_SERVER['SCRIPT_NAME']).'/index.php?nid=');
                $result =  $exceptionXsl->transformToXML($xml);
                echo $result;
            }
        }
    }
}


function nexista_devBuffer($init)
{
	$init->process();

	ob_start();
    ob_start();

    header( 'Cache-Control: no-cache, must-revalidate');
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

    nexista_development_console();

	$output = $init->run();


	if(isset($_GET['view_flow'])){
        if($_GET['view_flow']=="true"){
            nexista_view_flow();
        }
	}
    //$_SESSION['client_view_flow']
    if($_GET['client_view_flow']=="true") {
        $mynid = $_GET['nid'];
        $_SESSION['client_view_flow'] = "true";
    } elseif ($_GET['client_view_flow']=="false") {
        $mynid = $_GET['nid'];
        $_SESSION['client_view_flow'] = "false";
    }
    if($_SESSION['client_view_flow']=="true") {
        $flow_viewport = nexista_view_flow();
    }

    $output = str_replace("</body>","",$output);
    $output = str_replace("</html>","",$output);
    $output .= nexista_final_notices($cache_type,"dev");
    $output .= $flow_viewport;
    $output .= "</body></html>";
    $tidy = 0;
    if ($tidy) {
        $options = array("output-xhtml" => true, "indent" => true, "clean" => true);
        $output = tidy_parse_string($output, $options);
        tidy_clean_repair($output);
    }
    echo $output;


	ob_end_flush();
	header("Content-Length: ".ob_get_length());
	ob_end_flush();
}


function nexista_development_console()  {
$mylink = $_SERVER['SCRIPT_NAME'];
if ($_GET['nxrw_path']) {
    $mylink = $_GET['nxrw_path'];
}
$my_script = '<script type="text/javascript" src="'.$mylink.'?nid=x--dev--timex.js">&#160;</script>';
if($_SESSION['client_view_flow']=="true") {
    $my_script .= '<script src="'.$mylink.'?nid=x--dev--jquery.treeview.js" type="text/javascript">&#160;</script>';
    $my_script .= '<link rel="stylesheet" href="'.$mylink.'?nid=x--dev--jquery.treeview.css" />';
	$my_script .= '<link rel="stylesheet" type="text/css" href="'.$mylink.'?nid=x--dev--flow.css"/>';
	$my_script .= '<script type="text/javascript" src="'.$mylink.'?nid=x--dev--flow.js">&#160;</script>';
}

$in_head[] = array('string' => $my_script, 'priority' => 10);
Nexista_Flow::add("in_head",$in_head,false);

$my_uri = str_replace("&","&amp;",$_SERVER['REQUEST_URI']);



if(strpos($my_uri,"&amp;client_view_flow=true") || $_SESSION['client_view_flow']=="true") {
    $my_button = '[ <a href="'.str_replace("&amp;client_view_flow=true","",$my_uri).'&amp;client_view_flow=false">Hide Flow</a> ]';
} else {
    $my_button = '[ <a href="'.$my_uri.'&amp;client_view_flow=true">View Flow</a> ]';
}

// This button will rebuild the application, as well as purge the cache
$rebuild_button = '[ <span style="cursor: pointer;" onclick="$.post(\''.$mylink.'\', { \'x--dev--rebuild\': \'true\' }, function(data){
  document.getElementById(\'builder\').firstChild.nodeValue = \'Done\';
});">Rebuild</span> ]';

// This button will only purge the cache
$my_cache_purge = '[ <span style="cursor: pointer;" onclick="$.post(\''.$mylink.'\', { \'x--dev--purge\': \'true\' }, function(data){
  document.getElementById(\'purger\').firstChild.nodeValue = \'Done\';
});">Purge Cache</span> ]';


$admin_panel = <<<EOL
<script type="text/javascript">var began_loading = (new Date()).getTime();</script>
<table width="50%" cellpadding="2" style="background-color: #e3b6ec; right: 0; font-size: 9px; top: 0; z-index: 900; position: absolute; opacity: .1;" onmouseover="$(this).css('opacity','1.0');" onmouseout="$(this).css('opacity','0.2');">
<tr><td style="background-color: #e3b6ec;">
		$my_button $rebuild_button <span id="builder" style="color: red;">&#160;&#160;&#160;&#160;</span>
        Server:<span id="server_time"> 0.000 s </span>
        Client:<span id="client_time"> 0.000 s </span>
    </td>
    <td style="width:25%; background-color: #e3b6ec;">$my_cache_purge &#160;
    <span id="purger" style="color: red;"></span>
    </td>
	<td style="width:25%">
    </td></tr></table>

EOL;




$pre_body_content[] = array('string' => $admin_panel, 'priority' => 10);

Nexista_Flow::add("pre_body_content",$pre_body_content,false);

}



function nexista_view_flow() {
    $flow = Nexista_Flow::singleton('Nexista_Flow');

    // Transform into HTML form
    $use_xslt_cache="yes";
    if ($use_xslt_cache!="yes" || !class_exists('xsltCache')) {
        $debugXsl = new XsltProcessor();
    } else {
        $debugXsl = new XsltCache();
    }
    $xsl = new DomDocument;
    $xml_output = 1;
    if($xml_output===1) {
        $xsl->load(NX_PATH_BASE.'extensions/dev_buffer/s/xsl/flow.ul.xml.xsl');
    }
    $debugXsl->importStyleSheet($xsl);
    if(isset($_GET['ignore'])) {
        $debugXsl->setParameter('','ignore',$_GET['ignore']);
    } else {
        $debugXsl->setParameter('','ignore','i18n');
    }
    $debugXsl->setParameter('','link_prefix',dirname($_SERVER['SCRIPT_NAME']).'/index.php?nid=');
    return $debugXsl->transformToXML($flow->flowDocument);

}


/* This function outputs a small script used to pass the final processing time,
and to stop the client timer.. */
function nexista_final_notices($cacher=null, $mode) {
	$server_time = Nexista_Debug::profile();
$final_notices =  "
<script type=\"text/javascript\">
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
