<?php
/*
 * -File        config.php 
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2004, Nexista
 * -Author      joshua savage, 
 */

/**
 * @package     Nexista
 * @author Joshua Savage
 * @tutorial    config.pkg
 */

/**
 * The configuration system of Nexista is XML based and is designed to allow for 
 * multiple modes (i.e live, development, etc...).
 *
 * @package     Nexista
 */


Class Config
{

    
    /**
     * Hold an instance of the class
     *
     * @var     object
     */
     
    static private $instance;
      

    /**
     * Master config file (i.e. master.xml)
     *
     * @var     string
     */

    private $masterConfig;
    
    
    /**
     * Local config file (i.e. local.xml)
     *
     * @var     string
     */

    private $localConfig = null;
    
    /**
     * Config mode/environment
     *
     * @var     string
     */

    static public $mode = null;
    
      
    /**
     * SimpleXML root object of merged master/local
     *
     * @var     object
     */
     
    static public $xml;
     
     
    /**
     * Sets the master application config data
     * 
     * This method loads the config data that holds the applciation
     * parameters such as paths, location of sitemap, 
     * session preferences, db connections, etc...
     * Some of this data will be written directly in the gate files
     * during the compile process.
     *
     * @param   string      path to xml config file
     */
     
    public function setMaster($file)
    {
        if(!file_exists($file)) 
        {
            Error::init('Cannot find master config file: '.$file, NX_ERROR_FATAL);            
        }
        $this->masterConfig = $file;
    
       
    }
    
    
    /**
     * Sets the local override application config data
     *
     * This method accepts a local config file that can be used to:
     * 1. override master config settings
     * 2. create custom settings that can later be accessed in the standard manner
     * 
     * @param   string      path to xml config file
     */
     
    public function setLocal($file)
    {
		if($file) { 
			if(!file_exists($file)) 
			{
				Error::init('Cannot find local config file: '.$file, NX_ERROR_FATAL);            
			}
			$this->localConfig = $file;
		}
    }
    
    /**
     * Sets the configuration mode
     *
     * A mode is set in the config files for each entry in case of multiple
     * entries of the same type that may be used in different environment
     * such as live, dev, debug mode.
     * This mode, which is optional but if given it will be used by the config 
     * process to determine which entry to retrieve.
     * 
     * @param   string      mode
     */
     
    static public function setMode($mode)
    {
    
        self::$mode = $mode;      
      
    }
    
    /**
     * Retrieves the current mode
     *
     * @param   string      mode
     */
     
    static public function getMode()
    {
        return  self::$mode;
      
    }
     
    
    /**
     * Reads and parses all config data
     *
     * Reads the master and optional local config file into a common
     * simpleXML object
     * 
     */
     
    public function load()
    {
	$includepath=INCLUDE_PATH;
	$server_name=$_SERVER['SERVER_NAME'];
	if(defined("SERVER_NAME")) { 
        $server_name=SERVER_NAME;
    }
    $document_root='';
	$project_root=PROJECT_ROOT;
	$project_name=PROJECT_NAME;
    $app_name=APP_NAME;
    
    
	
    
	$directives='<!ENTITY includepath "'.$includepath.'">';
	$directives.='<!ENTITY server_name "'.$server_name.'">';
	$directives.='<!ENTITY project_root "'.$project_root.'">';
	$directives.='<!ENTITY project_name "'.$project_name.'">';
	$directives.='<!ENTITY app_name "'.$app_name.'">';
        //if a local config is passed we merge the two in a valid xml string
        $localfile = file_get_contents($this->localConfig);
        if(!empty($localfile))
        {
            preg_match('~<config>(.*)</config>~ms',$localfile, $u);
            preg_match('~<config>(.*)</config>~ms', file_get_contents($this->masterConfig), $g);
            self::$xml = simplexml_load_string('<?xml version="1.0"?><!DOCTYPE config ['.$directives.'
			]><config>'.$u[1].$g[1].'</config>');
           
           
        }
        else
        {
			//$nxpath=NX_PATH;
            preg_match('~<config>(.*)</config>~ms', file_get_contents($this->masterConfig), $n);
            self::$xml = simplexml_load_string('<?xml version="1.0"?><!DOCTYPE config ['.$directives.'
			]><config>'.$n[1].'</config>');
        }
      
    }    
    /**
     *
     * Reads the master 
     * 
     */
     
    public function loadMasterConfig()
    {
		
		// Would it be possible to check cache for masterConfig? YEAH!
		self::$xml = simplexml_load_file($this->masterConfig);
      
    }

    /**
     *
     * Return master config simple xml object
     * 
     */
     
    public function returnMasterConfig()
    {
		
		// Would it be possible to check cache for masterConfig? YEAH!
		$this->xml = simplexml_load_file($this->masterConfig);
      
    }
    
    /**
     *
     * Open entities skeleton and set table prefix.
     * 
     */
     
    public function customizeEntities()
    {
            $prefix=$_SERVER['SERVER_NAME'];
            $prefix="pb_";
            $directives='<!ENTITY prefix "'.$prefix.'">';
            $entitySkeleton = file_get_contents($this->masterConfig);
            $final = '<!DOCTYPE query [
'.$directives.'
'.$entitySkeleton.'
]><deletethisplaceholderinqueryhandler/>';
//<blank></blank>';
//          echo "<pre>"; echo $final; echo "</pre>"; exit;
self::$xml = simplexml_load_string($final);
            
    }
        
    
          
    /**
     * Writes a combined config file for runtime
     *
     * The master config and optional local config are output as a combined xml file
     * that is used by the runtime system
     * 
     */
       
    static public function writeConfig(&$config,$config_filename)
    {
        $canonical_filename = Config::get('./path/compile').$config_filename;
        $config_compile_error = 
            "Can't open $canonical_filename. Check permissions of parent directories, 
            or simply refresh to try and rebuild it.";
        
            if(is_dir(dirname($canonical_filename))) { 
                if($tmp = fopen($canonical_filename, "w+")) { 
                    if(flock($tmp, LOCK_EX))
                    {
                        fwrite($tmp, self::$xml->asXML());
                        flock($tmp, LOCK_UN);
                    } else {
                        $compile_path = Config::get('./path/compile');
                        if(!is_dir($compile_path) && is_writable(dirname(dirname($compile_path)))) {
                            `mkdir -p $compile_path`;
                        } else {
                            Error::Init( $config_compile_error,NX_ERROR_FATAL);  
                        }
                    }
                    fclose ($tmp);
                } else { 
                    $compile_path = Config::get('./path/compile');
                    if(!is_dir($compile_path) && is_writable(dirname(dirname($compile_path)))) {
                        `mkdir -p $compile_path`;
                    } else {
                        Error::Init( $config_compile_error,NX_ERROR_FATAL); 
                    }
                }
            } else { 
                $compile_path = Config::get('./path/compile');
                if(!is_dir($compile_path) && is_writable(dirname(dirname($compile_path)))) {
                    `mkdir -p $compile_path`;
                } else {
                    Error::Init( $config_compile_error."3",NX_ERROR_FATAL); 
                }
            }
    }
    
    
    /**
     * Retrieves a config value
     *
     * Retrieves a value for a config variable. If a mode is set
     * it will attempt to get the value for this variable in the preferred mode.
     * If nothing is found, it will then attempt to retrieve the default value in the
     * local config file and will finally look for the default master value.
     * Note that this method makes use of xpath and adds on the mode criteria
     * 
     * @param   string      variable path
     * @return  mixed       value or null if not found   
     */
       
    static public function get($name)
    {        
         $result = 0;
        //is this a parent node?
        
        if(!is_null(self::$mode))
        {     
            $result = self::$xml->xpath($name."[@mode='".self::$mode."'][not(*/node())]");
        }
        //no mode given or none found with a given mode
        if(!$result)
        {
            $result = self::$xml->xpath($name."[not(@mode)][not(*/node())]");
        }
            
        if($result)
        {
            //NOTE: simplexml returns objects so we need to convert or it makes a mess
                        
            //return as string otherwise
            return (string)$result[0];
        }
        else
            return null;
    }
    
    
    /**
     * Retrieves a config section
     *
     * Retrieves an array of values for a config section. If a mode is set
     * it will attempt to get the value for these variable in the preferred mode.
     * If nothing is found, it will then attempt to retrieve the default value in the
     * local config file and will finally look for the default master value.
     * 
     * @param   string      section path
     * @param   string      section id when using multiple sections with same name
     * @return  array       empty if nothing found   
     */
       
    static public function getSection($name, $id = false)
    {        
        
        if($id)
        {
				
            $res = self::$xml->xpath("//config/".$name."[@id='".$id."']");
            $obj=$res[0];
            
        }
        else
        {
        
            $res = self::$xml->xpath("//config/".$name."[not(@id)]");
             $obj=$res[0];
        }
      
        $result = array();
        if(is_object($obj))
        {
            foreach ($obj->children() as $k=> $v)
            {
                if(is_null(self::$mode) AND (!$v['mode'] OR (string)$v['mode'] == $mode ))
                {
                    $result[$k] = (string)$v;
                }
                //mode requested
                else
                {
                    //if child has mode match we use it
                    if((string)$v['mode'] === self::$mode)
                    {          
                        $result[$k] = (string)$v;
                    }
                    //get default value unless a moded one is already in
                    elseif(!isset($result[$k]))
                    {
                        $result[$k] = (string)$v;
                    }
                }
                
            }
        }
        else
        {
            Error::init('The "'.$name.'" section does not exist in the configuration', NX_ERROR_FATAL);
        }
        return $result;
    }
    
           
    /**
     * Returns a class singleton.
     *
     * @return  object      class singleton instance
     */
     
    static public function singleton() 
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }
} 

?>
