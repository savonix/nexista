<?php
/*
 * -File        init.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2004, Nexista
 * -Author      joshua savage
 */

/**
 * @package Nexista
 * @author Joshua Savage
 */
 

/**
 * Load required runtime files
 */
require_once(NX_PATH_CORE. "config.php");
require_once(NX_PATH_CORE. "error.php");
require_once(NX_PATH_CORE. "path.php");
require_once(NX_PATH_CORE. "flow.php");
require_once(NX_PATH_CORE. "debug.php");
require_once(NX_PATH_CORE. "auth.php");

/**
 * This class is reponsible for initializing the runtime process.
 *
 * It is responsible for loading certain required files,
 * initializing sessions, flow, etc.... and processing the 
 * correct files based on request and finally outputting the data.
 *
 * The default output functionality, Init::display(), can be overriden 
 * with a custom function as defined by Init::registerOutputHandler. This function 
 * will then call the output command Init::run() itself and perform any desired
 * pre-display processing such as cache the output, etc...
 *
 * See example site for an example registerOutputHandler.
 *
 * @package     Nexista
 */



class Init
{

    
    /**
     * Hold an instance of the class
     *
     * @var     object   
     */
     
    static private $instance;
    
    
    /**
     * Output handler
     *
     * @var     mixed   
     */
     
    static private $outputHandler;
    
    
    /**
     * Config object
     *
     * @var     object   
     */

    private $config;

    
    /**
     * Execution timer flag
     *
     * @var     boolean
     */

    public $timer = false;
    
    
    /**
     * Information array about currently processed gate
     *
     * @var     array
     */

    private $info = array();
    

    /**
     * Constructor - inits config
     *
     */
 
    public function __construct()
    { 
		
		$this->startTimer();
        $this->loadConfig(); 
        $this->setDebug();
    }
    
    
    /**
     * Startup functions, init...
     *
     * This method is responsible for calling all
     * startup functions such as timers, flow init, etc..
     *
     */
     
    public function start()
    {            
        Debug::register("in","total");
        $this->initSession();
        $this->initFlow();       
    }
    

    /**
     * Loads site configuration info
     *
     */

    public function loadConfig()
    {
        $this->config = Config::singleton();
        $this->config->setMaster(NX_PATH_COMPILE.'config.xml'); 
        $this->config->loadMasterConfig();
    }
    
    
    /**
     * Loads prepend file
     *
     * The prepend file is an optional file as defined in config.
     * While it can be used to hold any php code that the user may wish to
     * have processed everytime but is specifically designed to run code
     * before any output. Examples are headers and sessions.
     *
     * @param   mixed       valid php callback
     */

    public function loadPrepend($prepend)
    {
        require_once($prepend);
    }
    

    /**
     * Shutdown and cleanup
     *
     * This method is responsible for shutting down the
     * current request. It will clean up variables,
     * send ob to ouput, etc..
     *
     */

    public function stop()
    {
        Debug::register("out","total");
        exit();
    }

    
    /**
     * Init Flow
     *
     * This method creates an instance of Flow
     * which is the class responsible for all
     * data communication between sitemap modules (tags)
     *
     */

    private function initFlow()
    { 
        $flow = Flow::singleton();        
        $flow->init();       
    }

    
    /**
     * Init sessions
     *
     * This method starts up a session
     * and globalizes it.
     *
     */

    function initSession()
    {    
		

		$params = $this->config->getSection('session');
		// Also check for cache_limiter - if its public, no session!
		if($params['active']==0) { 
			return false;
		} else { 
		   
			if(!empty($params['cookieLifetime']))
				session_set_cookie_params($params['cookieLifetime']);
			if(!empty($params['cacheLimiter']))
				session_cache_limiter($params['cacheLimiter']);
			if(!empty($params['cacheExpires']))
				session_cache_expire($params['cacheExpires']);

			@session_start();
			define('NX_SESSION_ID', session_name().'='.session_id());
			
			return true;
		}
    }


    /**
     * Starts timer
     */

    private function startTimer()
    {
        $GLOBALS['debugStartTime'] = microtime();
    }



    
    /**
     * Reads sitemap for current gate data
     *   
     * This function retrieves the correct gate based on request.
     */

    public function process()
    {        
        // For command line, use require, for web, use require_once. - does it make a difference?
        require(NX_PATH_COMPILE.'sitemap.php');
        $this->info['uri'] = false;
        if(isset($gatesExact[$_ID_]))
        {
            $this->info['uri'] = $gatesExact[$_ID_]['uri'];
            if(isset($gatesExact[$_ID_]['cache']))
                $this->info['cacheExpiryTime'] = $gatesExact[$_ID_]['cache'];
            
            if(isset($gatesExact[$_ID_]['client_cache']))
                $this->info['clientCacheExpiryTime'] = $gatesExact[$_ID_]['client_cache'];
				
			if(isset($gatesExact[$_ID_]['role'])) {
                $this->info['requireRole'] = $gatesExact[$_ID_]['role'];
				// I wonder why this wasn't here in the first place?
				$auth = Auth::singleton();
				$auth->requireRole($this->info['requireRole']);
			}
            $gateFound=true;
        } 
        elseif(isset($gatesRegex)) 
        {
            foreach($gatesRegex as $regex=>$info) 
            {
				
				//$pattern = "/^(.".$pattern.")$/";
                if(preg_match("/".$regex."/",$_ID_,$match ))
                {
                    //globalize the match in case we need it
                    $GLOBALS['regex'] = $match;
                    $this->info['uri'] = $info['uri'];
                    if(isset($info['cache']))
                        $this->info['cacheExpiryTime'] = $info['cache'];
					if(isset($info['role'])) {
                        $this->info['requiredRole'] = $info['role'];
						$auth = Auth::singleton();
						$auth->requireRole($info['role']);
					}
                    $gateFound=true;
                    break;
                }
            }
          
        }  
        
        if(isset($gateMissing) && !isset($gateFound)) 
        {
            $this->info['uri'] = $gateMissing['uri'];
            if(isset($gateMissing['cache']))
                $this->info['cacheExpiryTime'] = $gateMissing['cache'];
                
            if(isset($gateMissing['role'])) {
                $this->info['requireRole'] = $gateMissing['role'];
                $auth = Auth::singleton();
                $auth->requireRole($this->info['requireRole']);
            }
        }
            
    }
           
    /**
     * Pocesses final output
     *   
     * This function processes the correct  gate based 
     * on request. In the event of not finding the 
     * correct gate, it will attempt to load the gate defined
     * under build/missing in config. Failure to do so
     * will result in a 404 redirect to be handled by the web server.
     */
     
    public function run()
    {
        if(!empty($this->info['uri'])) 
        {
            require(NX_PATH_COMPILE.$this->info['uri']);
            if(isset($debugOutput)) { 
				return $debugOutput.$output;
			} else { 
				return $output;
			}
        } 
        else
        {
            header('HTTP/1.x 404 Not Found');
            echo "Page not found: $error_file";
            echo $this->info['uri'];
            echo $_SERVER['SCRIPT_FILENAME'];
			return $output;
            
            exit;
            echo "<pre>";
            print_r($_GET);
            exit;
        }        
    }
    
    /**
     * Displays final output
     *   
     * This method is called from the loader file and is
     * responsible for activating the run process and displaying
     * the output. 
     * In the event that caching or an other pre-display processing
     * is needed, this method can be overriden using a callback function
     * registered with Init::registerOutputHandler();
     * This function needs to be a valid php callback (see call_user_func())
     * It needs to accept one argument, the reference to the Init object.
     * It also needs to call the static Init::run() method and capture/display
     * the returned string.
     *
     * Here is an example
     */

     public function display()
     { 
        //output handler defined?
        if(!is_null(self::$outputHandler)) 
        {            
            echo call_user_func(self::$outputHandler, $this);
        }
        //no output handler defined - just spit it out
        else
        {
            //process sitemap and locate file
            $this->process();
            ob_start();
            echo $this->run();
            ob_end_flush();
        }
     }    
	 
	/**
	 * Returns the gate info in an array consisting only of the params
	 * that are specified by the sitemap. Possible items include: 
	 * uri (always present), cache, and role. 
	 *
	 */
	public function getInfo($data)
	{
		if(isset($this->info[$data])) { 
			return $this->info[$data];
		} else { 
			return false;
		}
	}

     
    /**
     * Defines debug / error levels and reporting - this needs some help. 
     *
     */

    private function setDebug()
    {
        $configs = $this->config->getSection('runtime');
        if(isset($configs['debug']) && ((bool)$configs['debug']===true))
        {
            $GLOBALS['debugTrack'] = true;

        }
        return true;
    }

    
    /**
     * Registers a function to be called on output
     * 
     * This function can be used to manipulate the final output before it is
     * displayed. It can be used to create a caching system, compress output etc...
     *
     * The handler should be a callable function or array of object/method as
     * defined in is_callable php function.  It should accept 1 arguments:
     * 1. The reference to the init class instance
     *
     * Note that the Init::$info property contains the current cache value
     * from the sitemap if set.
     *
     * This function should call:
     *      $output = Init::run();
     * to get the final output. The function is responsible for displaying it.
     *
     * @param  mixed        a function or an array of class=>method
     */
     
    static public function registerOutputHandler($handler)
    {             
        if(is_callable($handler))   
            self::$outputHandler = $handler;
        else
            NexistaException::init("Init Output Handler is not callable!");
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

} //end class

?>