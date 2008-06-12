<?php
/**
 * -File        Error.php
 * -Copyright   Nexista
 * -Author      Joshua Savage
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
 * Define useful constants
 */
define('NX_ERROR_FATAL', 2);
define('NX_ERROR_WARNING', 3);
define('NX_ERROR_NOTICE', 4);


/**
 * The Nexista error system is Exception based and provides a method for calling
 * custom handler for each error type.
 *
 * The extension class used by Nexista extends from the standard PHP5 Exception
 * class with added functionality. It aims to be self supporting so that try/catch
 * blocks do not need to be called.
 *
 * Each place where an error might happen has the directive:
 * <code>Nexista_Error::init('message', ERROR_CODE, 'optional handler');</code>
 * When the error handling receives this call, it will verify if a handler has 
 * been defined. If so, it will call the handler with the error and the code and 
 * let it handle it. If no handler is specified, it will then see if the ERROR_CODE
 * is listed. The current standard error codes as NX_ERROR_FATAL (which halts the 
 * script), NX_ERROR_WARNING and NX_ERROR_NOTICE. If the error code does is not 
 * listed it will then throw the exception.
 *
 * Each class or functionality group can have it's own error codes and handlers
 * For example, a database class Nexista_might have:
 * <code>
 * if (!connectToDB())
 * {
 *     Nexista_Error::init('A DB connection could not be made',
 *      NX_ERROR_DB_CONNECT, 'errorHandler');
 * }
 *
 * function errorHandler($exception, $errorCode)
 * {
 *     switch($errorCode)
 *     {
 *         case NX_ERROR_DB_CONNECT:
 *             ...use alternate DB connection or retry
 *             break;
 *         default:
 *             throw($e);
 *             break;
 *     }
 *}</code>
 * In the case above, the error code indicated will cause an attempt to restore
 * the connection. 
 *
 * The error system does not log or display errors by default. Observers need
 * to be setup in the prepend file in order to do so:
 * <code>Error::addObserver('display', 'observerDisplay');
 *
 * function observerDisplay($e)
 * {
 *     //display the error
 *     $e-&gt;toText();
 *
 *     //do additional stuff such as log error, email webmaster, etc
 * }</code>
 * 
 * Credits: This class was inspired by the PEAR_Exception class
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@gmail.com>
 * @copyright 0000 Nexista
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

class Nexista_Error extends Exception
{

    /**
     * Exception callback
     *
     * @var string valid php callback
     */
    private $_handler;

    /**
     * Default error handler
     *
     * @var mixed
     */
    static private $_defaultHandler;

    /**
     * Registered observers
     *
     * @var array Observer callback functions
     */
    private static $_observers = array();

    /**
     * Current trace dump
     *
     * @var array Excpetion backtrace
     */
    private $_trace;

    /**
     * Instantiates a new Nexista Exception
     *
     * @param string $message  the error message
     * @param int    $code     the error code
     * @param string $_handler the handler
     *
     * @return null
     */
    public function __construct($message, $code = null, $_handler = null) 
    {

        $this->handler = $_handler;
        parent::__construct($message, $code);

        if (!$this->processHandler()) {
            $this->triggerObservers();

            switch($this->getCode()){
            case NX_ERROR_FATAL:
                @header("HTTP/1.1 500 Internal server error.");
                exit;
                break;

            case NX_ERROR_WARNING:
                break;

            case NX_ERROR_NOTICE:
                break;

            default:
                throw($this);
                break;

            }
        }
    }

    /**
     * Inits a new Exception with optional handler
     *
     * This static method is called instead of a throw command.
     * It instanciates a new exeception and allows for an optional
     * handler to be defined to handle the exception
     *
     * @param string $text     error message
     * @param int    $code     error code
     * @param mixed  $_handler a valid php callback handler
     *
     * @return null
     */
    static public function init($text, $code = null, $_handler= null)
    {

        $e = new Nexista_Error($text, $code, $_handler);

    }


    /**
     * Adds an observer function
     *
     * All registered observers are called when an exception occurs
     * and can be used to display,log, notify.
     *
     * @param string $name     name of the observer.
     * @param mixed  $callback a valid php callback handler
     *
     * @return null
     */
    static public function addObserver($name, $callback)
    {

        self::$_observers[$name] = array(
            'callback' => $callback,
            'active'   => true);
    }

    /**
     * Removes an observer
     *
     * @param string $name observer name
     *
     * @return null
     */
    static public function removeObserver($name)
    {
        unset(self::$_observers[$name]);
    }

    /**
     * Runs a user provided function to deal with this exception
     *
     * @return mixed function
     */
    protected function processHandler()
    {
        if (!is_null($this->handler) AND is_callable($this->handler)) {
            return call_user_func($this->handler, $this);
        } elseif (!is_null(self::$_defaultHandler)) {
            return call_user_func(self::$importHandler, $this);
        }
    }

    /**
     * Disable all or one observer
     *
     * When an external handler is used, it may be desirable not
     * to call the observer(s). This method can be used to do so.
     *
     * @param string $name (optional) observer name. If no name is given,
     * then all observers will be disabled
     *
     * @return null
     */
    public function disableObservers($name = null)
    {
        if (is_null($name)) {
            array_walk(self::$_observers,
                create_function('&$v,$k', '$v["active"]=0;'));
        } else {
            self::$_observers[$name]['active'] = false;
        }

    }

    /**
     * Enable previously disabled observer(s)
     *
     * @param string $name (optional) observer name. If no name is given,
     * then all observers will be enabled.
     *
     * @return null
     */
    public function enableObservers($name = null)
    {

        if (is_null($name)) {
            array_walk(self::$_observers,
                create_function('&$v,$k', '$v["active"]=1;'));
        } else {
            self::$_observers[$name]['active'] = true;
        }
    }


    /**
     * Activate observers
     *
     * This method is triggered by the Error::init() method when no handlers
     * are defined and will call all registered observers. It is up to the
     * handler, if defined, to call this method.
     *
     * @return null
     */
    public function triggerObservers()
    {
        foreach (self::$_observers as $func) {
            if (is_callable($func['callback']) AND $func['active'] === true) {
                call_user_func($func['callback'], $this);
                continue;
            }
        }
    }


    /**
     * Returns error trace
     *
     * @return array error backtrace
     */
    public function getTraceSafe()
    {
        if (!isset($this->_trace)) {
            $this->_trace = $this->getTrace();
            if (empty($this->_trace)) {
                $backtrace    = debug_backtrace();
                $this->_trace = array($backtrace[count($backtrace)-1]);
            }
        }
        return $this->_trace;
    }

    /**
     * Returns class where exception occured
     *
     * @return string error class
     */
    public function getErrorClass()
    {
        $trace = $this->getTraceSafe();
        return $trace[0]['class'];
    }

    /**
     * Returns method where exception occured
     *
     * @return string error function
     */
    public function getErrorMethod()
    {
        $trace = $this->getTraceSafe();
        return $trace[0]['function'];
    }


    /** 
     * Outputs error as XML
     *
     * @return string XML error
     */
    public function outputXml()
    {

        $xml[] = '<?xml version="1.0"?>';
        $xml[] = '<exception>';
        $xml[] = '<message>'.$this->getMessage().'</message>';
        if (defined('NX_DEBUG_INFO_GATE')) {
            $xml[] = '<gate>';
            $xml[] = '<name>'.NX_DEBUG_INFO_GATE.'</name>';
            $xml[] = '</gate>';
        }
        $trace = $this->getTraceSafe();

        $xml[] = '<traces>';
        foreach ($trace as $k => $v) {
            $xml[] = '<trace>';

            if (!empty($v['class'])) {
                $xml[] = '<class>'. $v['class'] . $v['type'].'</class>';
            }
            $xml[] = '<function>'.$v['function'].'</function>';

            $args = array();
            if (!empty($v['args'])) {
                foreach ($v['args'] as $arg) {
                    if (is_null($arg)) $args[] = 'null';
                    elseif (is_array($arg)) $args[] = 'Array';
                    elseif (is_object($arg)) $args[] = 'Object('.get_class($arg).')';
                    elseif (is_bool($arg)) $args[] = $arg ? 'true' : 'false';
                    elseif (is_int($arg) || is_double($arg)) $args[] = $arg;
                    else {
                        $arg = (string)$arg;
                        $str = htmlspecialchars(substr($arg, 0, 16));
                        if (strlen($arg) > 16) $str .= '...';
                        $args[] = "'" . $str . "'";
                    }
                }
            }

             $xml[] = '<args>'. implode(', ', $args) . '</args>';
             $xml[] = '<file>'. $v['file'] . '</file>';
             $xml[] = '<line>'. $v['line'] . '</line>';
             $xml[] = '</trace>';
        }
        $xml[] = '</traces></exception>';

        return implode("\n", $xml);
    }

    /**
     * Outputs error as string
     *
     * @return null
     */
    public function toText()
    {
        @header("Content-type: text/plain");
        echo $this->getTraceAsString();
    }


    /**
     * Registers a function to be called if no custom handlers are defined
     *
     * This method allows a callable function to be called for standard errors
     * (i.e. NX_ERROR_FATAL, NX_ERROR_WARNING) to override the default actions.
     * Note that if a custom handler for an error is defined in the Error:init()
     * arguments, this function will never be called.
     * This function should accept 1 argument: reference to the current Error
     *
     * @param mixed $_handler a function or an array of class=>method
     *
     * @return null
     */

    static public function registerDefaultHandler($_handler)
    {

        if (is_callable($_handler))
            self::$_defaultHandler = $_handler;
        else
            Nexista_Error::init("Error Default Handler is not callable!");
    }
}

?>
