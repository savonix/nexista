<?php
/**
 * -File        validator.php
 * -Copyright   Savonix Corporation
 * -Author      Albert Lash
 * -Author      joshua savage
 *
 * PHP version 5
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

/**
 * This class is the base class upon which to extend custom validators
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

class Nexista_Validator
{

    /**
     * Data requirement flag
     *
     * @var boolean
     */
    protected $required = true;


    /**
     * Data existence flag
     *
     * @var boolean
     */
    protected $empty = false;


    /**
     * Validator result flag
     *
     * @var boolean
     */
    protected $result = false;


    /**
     * Validation info message
     *
     * @var string
     */
    protected $message = '';


    /**
     * Function parameter array
     *
     * @var array
     */
    protected $params = array();


    /**
     * Applies validator and checks requirements
     *
     * @param array   &$params  class Nexista_parameters
     * @param string  $required item requirement parameters
     * @param boolean &$result  validator success
     *
     * @return boolean function success
     */
    public function process(&$params, $required, &$result)
    {
        $this->setRequired($required);

        if (!$this->applyParams($params)) {
            return false;
        }
        if (!$this->main()) {
            return false;
        }
        $result = $this->result;
        return true;
    }


    /**
     * Applies validator
     *
     * This function should be extended in the custom validators.
     * The function should return true/false for errors.
     * If the item to validate is empty, call the method $this->isEmpty().
     * Set the property $this->valid to record validator result.
     *
     * Sample code
     * <code>
     * //get data
     *  $data = time();
     * //check if empty value
     * if (!isset($data))
     *      $this->setEmpty();
     * //validate
     * if ($data = 1)
     *      $this->valid = true;
     * else
     *      $this->valid = false;
     * //return true or false for function errors
     * return true;
     * </code>
     *
     * @return boolean success
     */

    protected function main()
    {
        return true;
    }


    /**
     * Sets a message from validator
     *
     * This function allows a validator to set a text message that
     * can be used to present to the user as error. Please note that this
     * is not the error string itself. These error strings are language dependent
     * and not set by the validator. This is rather some useful information that
     * some validators may provide such as where and what kind of error caused
     * the failure of the validation process.
     *
     * @param string $message message to set
     *
     * @return null
     */

    protected function setMessage($message)
    {
        $this->message = $message;
    }


    /**
     * Checks item requirements
     *
     * @param string $required item requirements
     *
     * @return null
     */

    protected function setRequired($required)
    {

        if ($required === 'true') {
            $this->required = true;
        } elseif ($required === 'false') {
            $this->required = false;
        } else {
            $req = explode(',', $required);

            if (sizeof($req) == 2) {
                $var = Nexista_Flow::getByPath($req[0]);

                if (is_array($var)) {
                    $var = $var[0];
                }
                if (preg_match($req[1], $var, $match) == true) {
                    $this->required = true;
                } else {
                    $this->required = false;
                }
            } else {
                Nexista_Error::init("Validator 'required' field is not valid", 
                    NX_ERROR_WARNING);
                $this->required = true;
            }
        }
    }


    /**
     * Gets validator result
     *
     * @return boolean     validator result
     */

    public function isValid()
    {
        return $this->result;
    }


    /**
     * Sets data existence to absent
     *
     * @return null
     */

    public function setEmpty()
    {
        $this->empty = true;
    }


    /**
     * Gets data existence
     *
     * @return boolean true if present, false if absent
     */

    public function isEmpty()
    {
        return $this->empty;
    }


    /**
     * Gets data requirement flag
     *
     * @return boolean true if required, false if not
     */

    public function isRequired()
    {
        return $this->required;
    }


    /**
     * Gets a message from validator
     *
     * @return string message
     */

    public function getMessage()
    {
        if ($this->required && $this->empty)
            return "is required";
        return $this->message;
    }


    /**
     * Load class parameters
     *
     * This function will check if the required parameters
     * for this class are supplied and will load them into
     * $this->params array
     *
     * @param array &$params class parameters
     *
     * @return boolean success
     */

    protected function applyParams(&$params)
    {
        $cnt = 0;
        foreach ($this->params as $key => $val) {
            if (empty($params[$cnt]) && $val == 'required') {
                Nexista_Error::init('Class '. get_class($this).' does not have 
                    the required number of parameters', NX_ERROR_FATAL);
            }
            $this->params[$key] = $params[$cnt];
            $cnt++;
        }
        return true;
    }

} //end class

?>