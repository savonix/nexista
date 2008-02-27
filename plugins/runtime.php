<?php
/*
Program: Nexista Example Site
Component: runtime.php
Copyright 2003-2007, Albert L. Lash, IV
Savonix Corporation
License: LGPL
*/
    
//This is different depending on which web/file.php is called. 
$app_runtime=PROJECT_ROOT.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'lib/php'.DIRECTORY_SEPARATOR.'runtime.php';

$db_version = rtrim(file_get_contents(PROJECT_ROOT.DIRECTORY_SEPARATOR.'config/db_version.txt'));

if(is_file($app_runtime)) { 
    require($app_runtime);
}

# This wacky path builder is required due to mod_rewrite situations
$path = $_SERVER['REQUEST_URI'];
$path = dirname($path)."/".basename($_SERVER['SCRIPT_NAME']);
$path_prefix = dirname($path)."/";
//$app_prefix = "acc/".APP_NAME."/";
//$link_prefix = $path."?nid=".$app_prefix;
$link_prefix = $path."?nid=";
$right_now = date('Y-m-d H:i:s');

//$current_user_id = $_SESSION['NX_AUTH']['user_id'];
$current_user_id = 100;

if(class_exists("Nexista_Config")) { 
} else { 
    class Nexista_Config extends Config { 
    }
    class Nexista_Config extends Config { 
    }
    class Nexista_Flow extends Flow { 
    }
    class Nexista_Path extends Path { 
    }
}
$debug = Nexista_Config::get('./runtime/debug');
    
$top_left_logo = "images/pbooks-logo_120x60.png";

if($_SERVER['SERVER_NAME']=="www.livedemo.pbooks.org") { 
$footer_includes = <<< EOF
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script><script type="text/javascript">
_uacct = "UA-574944-19";
urchinTracker();
</script>
EOF;
} else { 
$footer_includes; 
}
$runtime = array('host_name'=>$_SERVER['SERVER_NAME'],
                'path_prefix'=>$path_prefix,
                'link_prefix'=>$link_prefix,
                'right_now'=>$right_now,
                'current_user_id'=>$current_user_id,
                'debug'=>$debug,
                'top_left_logo'=>$top_left_logo,
                'footer_includes'=>$footer_includes,
                'db_version'=>$db_version);

Nexista_Flow::add("runtime",$runtime,false);

// for use with command line admin and cli.xsl
global $in;
Nexista_Flow::add("test",$in,false);
