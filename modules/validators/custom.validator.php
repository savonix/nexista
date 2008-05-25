<?php
/*
 * -File        custom.validator.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista2002-2007, Nexista
 * -Author      joshua savage
 * -Author      albert lash
 */

/**
 * @package     Nexista
 * @subpackage  Validators
 * @author      Joshua Savage 
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