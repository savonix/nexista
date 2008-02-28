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
	//ob_start('ob_gzhandler');
    ob_start();
    
    header( 'Cache-Control: no-cache, must-revalidate, post-check=3600, pre-check=3600');
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
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


	if(isset($_GET['view_flow'])){
        if($_GET['view_flow']=="true"){
            view_flow();
        }
	}
    if($development_console===true) { 
        $output = str_replace("</body>","",$output);
        $output = str_replace("</html>","",$output);
        echo $output;
        final_notices($cache_type,"dev");
        echo "</body></html>";
    } else { 
        echo $output;
    }
    
	ob_end_flush();
	header("Content-Length: ".ob_get_length());
	ob_end_flush();
}


/* This function only used on development stage. */
function development_console()  {
    
$my_script = <<<EOL
	<script type="text/javascript">
	var began_loading = (new Date()).getTime();
	function done_loading() {
		var total = (((new Date()).getTime() - began_loading) / 1000);
		document.write(total);
	}
	</script>
EOL;

$in_head[] = array('string' => $my_script, 'priority' => 10);
Nexista_Flow::add("in_head",$in_head,false);

$my_uri = $_SERVER['REQUEST_URI'];
if(strpos($my_uri,"view_flow=true")) { 
    $my_button = '[ <a href="'.str_replace("&view_flow=true","",$my_uri).'">Hide Flow</a> ]';
} else { 
    $my_button = '[ <a href="'.$my_uri.'&view_flow=true">View Flow</a> ]';
}
$admin_panel = <<<EOL
  <table width="100%" cellpadding="2"><tr><td>
		$my_button
	</td>
	</tr></table>
EOL;
$pre_body_content[] = array('string' => $admin_panel, 'priority' => 10);
Nexista_Flow::add("pre_body_content",$pre_body_content,false);
}
 
function view_flow() { 
	$debugXsl = new XsltProcessor();
	$xsl = new DomDocument;
	$xsl->load(NX_PATH_BASE."plugins/dev_buffer/flow.xsl");
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
/*
$footer[] = array('string' => $final_notices, 'priority' => 1000);
Nexista_Flow::add("footer",$footer,false);
*/
}


