<?php
/**
 * -File        Custom.Validator.php
 * -Copyright   Savonix Corporation
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
 * This validator calls a user defined function to validate the data.
 * This function/method should be callable by 'call_user_func'. 
 * It needs to accept 2 parameters: 
 * 1. the data string to validate
 * 2. return the validation by reference
 * It also should return function success as true or false.
 * <code>
 * function myCallableFunction($data, $isValid)
 * {
 *      if($data = 'good')
 *          $isValid = true;
 *      else
 *          $isValid = false;
 *
 *      //return function execution status
 *      return true;
 * }</code>           
 *
 * @package     Nexista
 * @subpackage  Validators
 */


class Nexista_CustomValidator extends Nexista_Validator
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected $params = array(
        'var' => '', //required - name of flow var to validate
        'function' => ''   //required - user callable function
        );

    /**
     * Validator error message
     *
     * @var     string
     */

    protected $message = "is not acceptable";


    /**
     * Applies validator
     *
     * @return  boolean     success
     */

    public function main()
    {
        
        $data = Nexista_Path::get($this->params['var'], 'flow');
        if(!empty($data))
        {      
            if(is_callable($this->params['function']))
            {
                $res = call_user_func($this->params['function'],$data, &$this->result);
                return $res;
            }
            return false;
        }
        $this->setEmpty();
        return true;
        
    }

} //end class
?>