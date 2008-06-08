<?php
/**
 * -File        ValidatorHandlerData.php
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
 * This class holds data from a validation procedure
 * ran from ValidationHandler class. It is accessed from Flow
 * and holds data such as which fields failed, messages, etc..
 *
 * @package     Nexista
 * @subpackage  Handlers
 */

class Nexista_ValidatorHandlerData
{

    /**
     * array to hold validator data such as which fields failed and how
     *
     * @var     array
     */

    public $validatorData = array();


    /**
     * overall pass/fail criteria of the validation process
     *
     * @var     boolean
     */

    public $success;



    /**
     * Constructor - Intializes stuff
     *
     */

    public function __construct()
    {
        //assume validation true until fail
        $this->success = true;
    }


    /**
     * Sets pretty text for an item
     *
     * This is used when optionally showing what form
     * fields were bad and a name is needed to describe what the 
     * item in question is.
     *
     * @param   string      name of failed var
     * @param    string      pretty text
     */

    public function itemMessage($name, $text)
    {
        $this->validatorData[$name]['text'] = $text;
    }


    /**
     * Sets pretty text for a validator
     *
     * This is used when optionally showing what form
     * fields were bad and a name is needed to describe what the 
     * item in question is.
     *
     * @param   string  name of failed var
     * @param   string  name of failed validator
     * @param    string  pretty text
     */

    public function validatorMessage($name, $val, $text)
    {
        $this->validatorData[$name][$val]['error'] = $text;
    }

    /**
     * Sets individual item Fail
     *
     * @param   string  name of failed var
     * @param    string  criteria that failed
     *
     */

    public function itemFail($name, $criteria, $reason)
    {
        $this->validatorData[$name]['failed'][$criteria] = $reason;
        $this->setFail();
    }


    /**
     * Sets overall fail/pass
     *
     */

    public function setFail()
    {
        $this->success = false;
    }


    /**
     * Gets overall fail/pass
     *
     * @return  boolean overall form success
     */

    public function getResult()
    {
        return $this->success;
    }

} //end class

?>