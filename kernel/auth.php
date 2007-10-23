<?php
/*
 * -File        auth.php 
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2004-2007, Nexista
 * -Author      joshua savage
 * -Author      albert lash
 */

/**
 * @package     Nexista 
 * @author      Joshua Savage 
 */

/**
 * The Nexista Auth model is setup to allow user specified handlers to be used 
 * in order to provide whatever functionality is desired. A developer can setup 
 * their preferred authentication system and hook it into the nexista core.
 * 
 * Each gate can be setup in the sitemap to require a <i>role</i>. PHP Auth methods 
 * can also be used in scripts where a particular role is required, though with 
 * judicious use, the gate role is usually all that is needed.
 *
 * Note that we us the term <i>role</i> which may imply a role-based system but 
 * since all of the inner user-group-role workings are handled by custom functions 
 * it does not matter to nexista what system is used. One could use a complex role 
 * based system or a simple one password / one user, as desired.
 *
 * To use the Authentication system, the developer needs to implement a custom 
 * Login handler and optionally a Denied, Expired and Timeout handler in the prepend 
 * file. If a user is not logged in and encounters a role requirement, the login 
 * handler will be called. This handler is responsible for authenticating the user 
 * in whatever manner is preferred, then calling the Auth::registerUser() method 
 * with an array of allowed roles for this user. 
 *
 * If the session of a logged in user times out, expires or if the user encounters 
 * a role they do not have, the Timeout, Expired and Denied handler will be called 
 * respectively if setup. If the required handler is not setup, then it will default 
 * to the Login handler.
 *
 * @package     Nexista
 */

 
class Auth
{

    /**
     * user status - logged in
     */
     
    const NX_AUTH_STATUS_ACTIVE = 0;
    
    
    /**
     * user status - inactive
     */
     
    const NX_AUTH_STATUS_INACTIVE = 1;
    
    
    /**
     * user status - timed out
     */
     
    const NX_AUTH_STATUS_TIMEOUT = 2;
    
    
    /**
     * user status - expired
     */
     
    const NX_AUTH_STATUS_EXPIRED = 3;
        
    
    /**
     * Hold an instance of the class
     */
     
    static private $instance;
    
    
    /**
     * User defined handler to process on login
     *
     * @var mixed function or object=>method 
     */
     

    static private $loginHandler;
    
    
    /**
     * User defined handler to process on session timeout
     *
     * @var mixed function or object=>method 
     */

    static private $timeoutHandler;
    
    
    /**
     * User defined handler to process on session expiry
     *
     * @var mixed function or object=>method 
     */

    static private $expiredHandler;
    
    
    /**
     * User defined handler to process on denied access
     *
     * @var mixed function or object=>method 
     */

    static private $deniedHandler;
       
    
    /**
     * Session name
     *
     * @var string
     */

    private $sessionName = 'NX_AUTH';
    
    
    /**
     * User session data
     *
     * This array keeps track of certain items such as user roles,
     * if they are identified, what url they came from etc...
     * It is stored in sessions.
     * 
     * @var     array
     */
     
    public $sessionData = false;
    
    
    /**
     * Constructor - Inits session, config
     *
     */

    public function __construct()
    {
		$sessions = Config::get('session/active');
		if($sessions==0) { 
			return;
		} else { 
			@session_start();
			
			//user already identified?
			if(isset($_SESSION[$this->sessionName]))
			{
			   $this->sessionData= & $_SESSION[$this->sessionName];
			   
			}
			//newbie
			else
			{
				//setup a new session
				$_SESSION[$this->sessionName] = array();
				$this->sessionData =& $_SESSION[$this->sessionName];
				$this->sessionData['status'] = self::NX_AUTH_STATUS_INACTIVE;
				
			}
		}
       
    }

    /**
     * Initialize a new auth session with config parameters
     *
     */
     
    private function initSession()
    {
        $params = Config::getSection('auth');
        
        //Time in min till expiry from start of session (0=never expire)
        $this->sessionData['expireTime'] = !empty($params['expire']) ?      $params['expire'] : '0';
        
        //Time in min till expiry from inactivity (0=never expire)
        $this->sessionData['idleTime'] = !empty($params['idle']) ? $params['idle'] : '0';
      
        //user is logged in
        $this->sessionData['status'] = self::NX_AUTH_STATUS_ACTIVE;
        
         //set login time
        $this->sessionData['lastTime'] = time();
    
    }


    /**
     * Require that the user has a particular role
     *
     * These roles are called using the sitemap tag as:
     * <code><map:gate name="somegate" role="editArticle"></code>
     * or in a PHP script as:
     * <code>$auth = Auth::singleton();
     * if(auth-&gt;requireRole('someRole'))
     * {
     *      ...do my stuff
     * } </code>
     *
     * @param   string      role for this action/gate
     * @return  boolean     if user has role permission
     */
     
    public function requireRole($role)
    {
        $this->sessionData['requestedRole'] = $role;
        $this->sessionData['requestedUrl'] = $_SERVER['REQUEST_URI'];
        
       
        //check current session
        $this->checkStatus();

        switch($this->sessionData['status'])
        {
			
            //user logged in - check roles  
            case self::NX_AUTH_STATUS_ACTIVE:
            
                for($i = 0; $i < count($this->sessionData['roles']); $i++)
                {
                    if($this->sessionData['roles'][$i] == $role)
                    {
                        return true;
                    }
                }
                //user does not have proper role, deal with that
                if(!is_null(self::$deniedHandler)) 
                    call_user_func(self::$deniedHandler, $this);
                else
                {
                    Error::init('No auth denied function defined', NX_ERROR_FATAL);
                }
                return false;
            
                break;
           
                
            //session timed out from inactivity
            case self::NX_AUTH_STATUS_TIMEOUT:
            
                //call login handler, or die if none
                if(!is_null(self::$timeoutHandler)) 
                {
                    call_user_func(self::$timeoutHandler, $this);
                }                
                //fallback on login handler if possible
                elseif(!is_null(self::$deniedHandler)) 
                {	
                    call_user_func(self::$loginHandler, $this);
                }
                else
                {
                    Error::init('No auth timeout handler defined', NX_ERROR_FATAL);
                    
                }
                break;
               
            //user session expired 
            case self::NX_AUTH_STATUS_EXPIRED:
            
                //call login handler, or die if none
                if(!is_null(self::$expiredHandler)) 
                {
                    call_user_func(self::$expiredHandler, $this);
                }
                //fallback on login handler if possible
                elseif(!is_null(self::$loginHandler))
                {
                    call_user_func(self::$loginHandler, $this);
                }
                else
                {
                    Error::init('No auth expired handler defined', NX_ERROR_FATAL);
                }
            
                break;
              
                 
            //user is not logged in
            default:
            case self::NX_AUTH_STATUS_INACTIVE:
                
                //call login handler, or die if none
                if(!is_null(self::$loginHandler)) 
                    call_user_func(self::$loginHandler, $this);
                else
                {
                    Error::init('No auth login handler defined', NX_ERROR_FATAL);            
                }
            
                break;            
                
        }
        
    }


    /**
     * Registers a user with valid roles
     *
     * This methid is used by the login handle to register (login) 
     * the user with an array of allowed roles. These roles are called either in the 
     * sitemap tag as:
     * <code>&lt;map:gate name=&quot;somegate&quot; role=&quot;editArticle&quot;&gt;</code>
     * or in a PHP script as:
     * <code>$auth = Auth::singleton();
     * if(auth-&gt;requireRole('someRole'))
     * {
     *     ...do my stuff
     * } </code>
     *
     * @param   array       User info to register with sessions
     * @return  boolean     success
     */

    public function registerUser($roles = array())
    {
        //initialize session with some config parameters
        $this->initSession(); 
            
        if(!is_array($roles))
            $roles = array($roles);
        
        //add roles
        $this->sessionData['roles'] = $roles;
        

        return true;
    }


    /**
     * Sets the auth session name
     *
     * @param   string      session name
     */

    public function setSessionName($name)
    {
        $this->sessionName = $name;
    }
    
    
    /**
     * Returns user session data
     *
     * If a variable name is passed, the value for it will
     * be returned. If no name is passed, the entire array will
     * be returned
     *
     * @param   string      (optional) user session value name
     * @return  mixed       value of requested variable, or whole session data
     */

    public function getSessionData($name = false)
    {
       
        if($name)
        {
            return isset($this->sessionData[$name])? $this->sessionData[$name] : null;
        }
        else
            return $this->sessionData;
    }
    
    
    /**
     * Sets a user session data value
     *
     * This method will set a variable value in the user session
     *
     * @param   string      user session variable name
     * @param   string      user session variable value
     */

    public function setSessionData($name, $value)
    {
        $this->sessionData[$name] = $value;
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

    
    /**
     * Delete all session information and logout the user.
     *
     */

    public function removeUser()
    {
        unset($_SESSION[$this->sessionName]);

        $this->sessionData = array();
    }
    
    
    /**
     * Checks a user's current state
     * 
     * This method will return the current user status as a constant:
     * - Auth::NX_AUTH_STATUS_ACTIVE (user is logged in)
     * - Auth::NX_AUTH_STATUS_EXPIRED (user session expired)
     * - Auth::NX_AUTH_STATUS_TIMEOUT (user session timeout from inactivity)
     * - Auth::NX_AUTH_STATUS_INACTIVE (user is not yet logged in)
     * 
     */

    public function checkStatus()
    {
      
        //expired?
        if($this->sessionData['status'] === self::NX_AUTH_STATUS_ACTIVE)
        {
            //idled out?
            if($this->sessionData['idleTime'] > 0)
            {
                //yes
                if(($this->sessionData['lastTime'] + ($this->sessionData['idleTime'] * 60))
                    < time())
                {
                    $this->sessionData['status'] = self::NX_AUTH_STATUS_TIMEOUT;
                    return;
                }
            }
            //expired?
            if($this->sessionData['expireTime'] > 0)
            {
                //yes
                if(($this->sessionData['lastTime'] + ($this->sessionData['expireTime'] * 60))
                    < time())
                {
                    $this->sessionData['status'] = self::NX_AUTH_STATUS_EXPIRED;
                    return;
                }
            }
            //all okay - update check in time
            $this->sessionData['lastTime'] = time();
            return;
            
        }

    }
    
      
    /**
     * Registers a function to be called on auth login
     * 
     * This function will be called when the user needs a specific role
     * for an action and has not yet logged in.
     * It could be used to show a login screen or somehow inform user
     * that the action needs a special role.
     *
     * @param  mixed        function or an array of class=>method
     */
     
    static public function registerLoginHandler($handler)
    {             
        if(is_callable($handler))   
            self::$loginHandler = $handler;
        else
            Error::init('Auth Login Handler is not callable!', NX_ERROR_FATAL);
    }    
     
    /**
     * Registers a function to be called on auth denied access
     * 
     * This function will be called when a user, who is already logged in
     * somehow, encounters a role beyond their access level.
     * It might be used to offer the user to upgrade their level or present
     * them with a denied screem.
     *
     * @param  mixed        function or an array of class=>method
     */
     
    static public function registerDeniedHandler($handler)
    {
        if(is_callable($handler))   
            self::$deniedHandler = $handler;
        else
            Error::init('Auth Denied Handler is not callable!', NX_ERROR_FATAL);  
    }
    
    
    /**
     * Registers a function to be called on auth session expiry
     * 
     * This function will be called when the user's session expires.
     * It might be used to reshow a login screen.
     *
     * @param  mixed        function or an array of class=>method
     */
     
    static public function registerExpiredHandler($handler)
    {
        if(is_callable($handler))   
            self::$expiredHandler = $handler;
        else
            Error::init('Auth Expiry Handler is not callable!', NX_ERROR_FATAL);
    }
    
    
    /**
     * Registers a function to be called on auth session timeout
     * 
     * This function will be called when the user's session times out from inactivity.
     * It might be used to reshow a login screen.
     *
     * @param  mixed        function or an array of class=>method
     */
     
    static public function registerTimeoutHandler($handler)
    {

        if(is_callable($handler))   
            self::$timeoutHandler = $handler;
        else
            Error::init('Auth Timeout Handler is not callable!', NX_ERROR_FATAL);

    }

   

}


?>
