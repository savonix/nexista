<?php
/* <!--
Plugin Name: Development Output Buffer
Plugin URI:
Description:
Version: 0.1
Copyright: Savonix Corporation
Author: Albert Lash
License: LGPL

TODO:
    1. Add a hook to nexista_cache for debugging purposes.
    2. Add custom error handling for more detailed error reports when developing.

--> */


/* START EXCLUSIONS CHECK */
/*
This section is always processed. It checks for exlusions to the
development output buffer.
*/
$tidy_it = true;
$excludes = Nexista_Config::get('./extensions/tidy/excludes');
if(strpos($excludes,',')) {
    $x_array = explode(',',$excludes);
} else {
    if(!empty($excludes)) {
        $x_array[] = $excludes;
    }
}


if(!empty($x_array)) {
    // this could be slow, might want to have a setting to turn on / off
    foreach($x_array as $value) {
        if(eregi($value,$_GET['nid'])) {
            unset($tidy_it);
        }
    }
}

/* END EXCLUSIONS */



if($tidy_it===true) {
    Nexista_Init::registerOutputHandler('tidy_output');
}
if (!function_exists('tidy_output')) {
    Nexista_Error::addObserver('display', 'tidy_output');

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
            $use_xslt_cache = 'yes';
            if ($use_xslt_cache!='yes' || !class_exists('xsltCache')) {
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


function tidy_output($init)
{
	$init->process();

	ob_start();
    ob_start();

	$output = $init->run();

    $tidy = 'xhtml';
    if ($tidy=='xhtml') {
        $options = array('output-xhtml' => true, 'indent' => true, 'input-encoding' => 'utf8','output-encoding' => 'utf8');
        $output = tidy_parse_string($output, $options);
        tidy_clean_repair($output);
    }
    if ($tidy=='html') {
        $options = array('output-html' => true, 'indent' => true, 'input-encoding' => 'utf8','output-encoding' => 'utf8', 'clean' => true);
        $output = tidy_parse_string($output, $options);
        tidy_clean_repair($output);
    }
    echo $output;


	ob_end_flush();
	header('Content-Length: '.ob_get_length());
	ob_end_flush();
}


