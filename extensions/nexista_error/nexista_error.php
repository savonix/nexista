<?php
/*
Plugin Name: Development Output Buffer
Plugin URI:
Description:
Version: 0.1
Copyright: Nexista
Author: Albert Lash
License: LGPL
*/



$development_console = 0;

Nexista_Error::addObserver('display', 'Nexista_generalError');

/**
 * Error...
 *
 * @param object $e error object
 *
 * @return null
 */

function Nexista_generalError($e)
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
        $my_xsl_file = NX_PATH_BASE.'extensions/nexista_error/s/xsl/exception.xsl';
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


