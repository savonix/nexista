<?php
/**
 * -File        init.php
 * -Copyright   Nexista
 * -Author      joshua savage
 * -Author      Albert Lash
 *
 * PHP version 5
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@gmail.com>
 * @copyright 0000 Nexista
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

/**
 * Load required runtime files
 */
require_once NX_PATH_CORE . "config.php";
require_once NX_PATH_CORE . "session.php";
require_once NX_PATH_CORE . "error.php";
require_once NX_PATH_CORE . "path.php";
require_once NX_PATH_CORE . "flow.php";
require_once NX_PATH_CORE . "debug.php";
require_once NX_PATH_CORE . "auth.php";


/**
 * This class is reponsible for initializing the runtime process.
 *
 * It is responsible for loading certain required files,
 * initializing sessions, flow, etc.... and processing the 
 * correct files based on request and finally outputting the data.
 *
 * The default output functionality, Nexista_Init::display(), can be overriden
 * with a custom function as defined by Nexista_Init::registerOutputHandler.
 * This function will then call the output command Nexista_Init::run() itself
 * and perform any desired pre-display processing such as cache the output, etc
 *
 * See example site for an example registerOutputHandler.
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@gmail.com>
 * @copyright 0000 Nexista
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */


class Nexista_Init
{


    /**
     * Hold an instance of the class
     *
     * @var object
     */

    static private $_instance;


    /**
     * Output handler
     *
     * @var mixed
     */

    static private $_outputHandler;


    /**
     * Config object
     *
     * @var object
     */

    private $_config;

    
    /**
     * Execution timer flag
     *
     * @var boolean
     */

    public $timer = false;


    /**
     * Information array about currently processed gate
     *
     * @var array
     */

    private $_info = array();


    /**
     * Constructor - inits config
     *
     * @return null
     */

    public function __construct()
    {

        $this->_startTimer();
        $this->loadConfig();
        $this->_setDebug();
    }


    /**
     * Startup functions, init...
     *
     * This method is responsible for calling all
     * startup functions such as timers, flow init, etc..
     *
     * @return null
     */

    public function start()
    {
        Nexista_Debug::register("in", "total");
        $this->_initFlow();
    }


    /**
     * Loads site configuration info
     *
     * @return null
     */

    public function loadConfig()
    {
        $this->config = Nexista_Config::singleton();
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
     * @param mixed $prepend valid php callback
     *
     * @return null
     */

    public function loadPrepend($prepend)
    {
        include_once $prepend;
    }


    /**
     * Shutdown and cleanup
     *
     * This method is responsible for shutting down the
     * current request. It will clean up variables,
     * send ob to ouput, etc..
     *
     * @return null
     */

    public function stop()
    {
        Nexista_Debug::register("out", "total");
        exit();
    }


    /**
     * Init Flow
     *
     * This method creates an instance of Flow
     * which is the class responsible for all
     * data communication between sitemap modules (tags)
     *
     * @return null
     */

    private function _initFlow()
    { 
        $flow = Nexista_Flow::singleton();
        $flow->init();
    }


    /**
     * Init sessions
     *
     * This method starts up a session
     * and globalizes it.
     *
     * @return null
     */

    function initSession()
    {
        $this->session = Nexista_Session::singleton();
        $this->session->start();
    }


    /**
     * Starts timer
     *
     * @return null
     */

    private function _startTimer()
    {
        $GLOBALS['debugStartTime'] = microtime();
    }


    /**
     * Reads sitemap for current gate data
     *
     * This function retrieves the correct gate based on request.
     *
     * @return null
     */

    public function process()
    {
        include NX_PATH_COMPILE . 'sitemap.php';

        $this->info['uri'] = false;

        if (isset($gatesExact[$_ID_])) {

            $this->info['uri'] = $gatesExact[$_ID_]['uri'];

            if (!isset($gatesExact[$_ID_]['nosession']))
                $this->initSession();

            if (isset($gatesExact[$_ID_]['cache']))
                $this->info['cacheExpiryTime'] = $gatesExact[$_ID_]['cache'];

            if (isset($gatesExact[$_ID_]['client_cache'])) {
                $this->info['clientCacheExpiryTime'] = 
                    $gatesExact[$_ID_]['client_cache'];
            }

            if (isset($gatesExact[$_ID_]['content_type']))
                $this->info['content_type'] = $gatesExact[$_ID_]['content_type'];

            if (isset($gatesExact[$_ID_]['role'])) {
                $this->info['requireRole'] = $gatesExact[$_ID_]['role'];

                $auth = Nexista_Auth::singleton();
                $auth->requireRole($this->info['requireRole']);
            }
            $gateFound = true;

        } elseif (isset($gatesRegex)) {
            foreach ($gatesRegex as $regex=>$_info) {

                if (preg_match("/".$regex."/", $_ID_, $match)) {

                    $GLOBALS['regex'] = $match;

                    $this->info['uri'] = $_info['uri'];

                    if (isset($_info['cache']))
                        $this->info['cacheExpiryTime'] = $_info['cache'];

                    if (isset($_info['role'])) {
                        $this->info['requiredRole'] = $_info['role'];
                        $auth = Nexista_Auth::singleton();
                        $auth->requireRole($_info['role']);
                    }

                    if (!isset($_info['nosession']))
                        $this->initSession();

                    $gateFound = true;
                    break;
                }
            }
        }


        if (isset($gateMissing) && !isset($gateFound)) {
            $this->info['uri'] = $gateMissing['uri'];
            if (isset($gateMissing['cache']))
                $this->info['cacheExpiryTime'] = $gateMissing['cache'];

            if (isset($gateMissing['role'])) {
                $this->info['requireRole'] = $gateMissing['role'];
                
                $auth = Nexista_Auth::singleton();
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
     *
     * @return null
     */

    public function run()
    {
        if (!empty($this->info['uri'])) {
            include NX_PATH_COMPILE . $this->info['uri'];
            if (isset($debugOutput)) {
                return $debugOutput.$output;
            } else {
                return $output;
            }
        } else {
            header('HTTP/1.x 404 Not Found');
            echo "Page not found: $error_file";
            echo $this->info['uri'];
            echo $_SERVER['SCRIPT_FILENAME'];
            return $output;
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
     * @return null
     */

    public function display()
    {
        //output handler defined?
        if (!is_null(self::$_outputHandler)) {
            echo call_user_func(self::$_outputHandler, $this);
        } else {
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
     * @param string $data path to resolve/render
     *
     * @return boolean
     */
    public function getInfo($data)
    {
        if (isset($this->info[$data])) {
            return $this->info[$data];
        } else {
            return false;
        }
    }


    /**
     * Defines debug / error levels and reporting
     *
     * @return boolean true or false
     */

    private function _setDebug()
    {
        $_configs = $this->config->getSection('runtime');
        if (isset($_configs['debug']) &&
                ((bool)$_configs['debug']===true ||
                $_configs['debug']===true)) {

            $GLOBALS['debugTrack'] = true;

        } else {

            $GLOBALS['debugTrack'] = false;

        }
        return true;
    }


    /**
     * Registers a function to be called on output
     *
     * This function can be used to manipulate the final output before it is
     * displayed. It can be used to create a caching system, compress output
     *
     * The handler should be a callable function or array of object/method as
     * defined in is_callable php function.  It should accept 1 arguments:
     * 1. The reference to the init class instance
     *
     * Note that the Init::$_info property contains the current cache value
     * from the sitemap if set.
     *
     * This function should call:
     *      $output = Init::run();
     * to get the final output. The function is responsible for displaying it.
     *
     * @param mixed $handler a function or an array of class=>method
     *
     * @return null
     */

    static public function registerOutputHandler($handler)
    {
        if (is_callable($handler))
            self::$_outputHandler = $handler;
        else
            Nexista_Error::init("Init Output Handler is not callable!");
    }


    /**
     * Returns a class singleton.
     *
     * @return object class singleton instance
     */

    static public function singleton()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }

        return self::$_instance;
    }

} //end class

?>