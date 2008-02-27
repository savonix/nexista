<?php

if(isset($nexista_path)) { 
    define('INCLUDE_PATH', $nexista_path);
} else { 
    define('INCLUDE_PATH', "/usr/share/php/");
}

if(!defined('SERVER_NAME')) { 
    define('SERVER_NAME', $server_name);
}
if(!file_exists(INCLUDE_PATH.'nexista/kernel/foundry.php')) { 
    echo "I can't find the nexista foundry class, and cannot continue. Try this:<br/><br/>";
    echo "<a href='http://www.nexista.org'>http://www.nexista.org</a>, and so you know, I looking here: <br/>";
    echo INCLUDE_PATH."nexista/kernel/foundry.php";;
    exit;
} else { 
    require(INCLUDE_PATH.'nexista/kernel/foundry.php');
}
$foundry = Foundry::singleton();
$config = PROJECT_ROOT.'/config/config.xml';
$app_config = PROJECT_ROOT.'/apps/'.APP_NAME.'/config/config.xml';
$app_entities = PROJECT_ROOT.'/apps/'.APP_NAME.'/config/entities.xml';

    if(!file_exists($config)) { 
        echo "Uh-oh, we already ran into a problem. I can't find a config file! I'm looking for $config";
        exit;
    }

    
    $config_file = file_get_contents($config);
        
    if(strpos($config_file,"@@database")) { 
        require(PROJECT_ROOT."/apps/pbooks/lib/php/install.php");
        exit;
    }
    if(!isset($mode)) { 
        $mode="dev";
    }
    if(isset($_ENV['NEXISTA_MODE'])) { 
        $mode=$_ENV['NEXISTA_MODE'];
    }
    if(!file_exists($app_config)) {
        $foundry->configure($config,NULL, $mode);
    } else {
        $foundry->configure($config,$app_config, $mode);
    }

    if(file_exists($app_entities)) {
        // Need to pass full path. Need to check for freshness!
        $compilePath = $foundry->getCompilePath();
        if(!file_exists($compilePath."entities.xml")) { 
            $foundry->defineEntities($app_entities,NULL,$compilePath."entities.xml");
        }
    }
    
    
    
    if(!file_exists($app_config)) {
        $foundry->configure($config,NULL, $mode);
    } else {
        $foundry->configure($config,$app_config, $mode);
    }

    
    
$foundry->debug = 1;

$my_sitemap = $foundry->getSitemapPath('./build/sitemap');

function check_freshness() { 
    global $config;
    global $app_config;
    global $foundry;
    global $server_init;
    global $my_sitemap;
    $last_build_time = filemtime($server_init); 
    if(file_exists($app_config)) { 
        if($last_build_time < filemtime($app_config)) { 
                $app_config_stat = false;
        } else { 
                $app_config_stat = true;
        }
    }
           
    
    if($last_build_time < filemtime($my_sitemap) || $last_build_time < filemtime($config) || $app_config_stat === false) { 
    
  
        build_it_now();      
        if($foundry->debug==1) { 
            echo "Nexista is rebuilding the loader because either the sitemap or one of the configs has been edited.<br/>";
            echo "<a href='javascript:history.go(-1)'>OK, all done, go back.</a><br/>";
            echo "Building index file....OK<br/>";

        }

    }
} 
    
function build_it_now() { 
    global $config;
    global $foundry;
    global $server_init;
    ob_end_clean();
    header( 'Cache-Control: no-cache, no-store');
    ?>
    <html>
    <body style="padding: 150px; font-family: verdana;">
    
    <?php 
    
        
    echo "Looks like you are installing to $server_init. Cool! <br/><br/>";
    
    
    ?>
    
    I'm guessing that your php is install in "/usr/share/php", and that you've already installed
    nexista in there. <br/><br/>
    
    <?php

        $foundry->buildLoader();
        $foundry->buildGates();  
        $foundry->buildSitemap();
?>

<script type="text/javascript">
setTimeout('top.location.reload()',1000);
</script>
<?php       
ob_flush();

        //echo "<pre>"; print_r($GLOBALS);

}
