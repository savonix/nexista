<?php
/*
 * -File        email.validator.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage
 */

/**
 * @package     Nexista
 * @subpackage  Validators
 * @author      Joshua Savage
 */
 
/**
 * This validator is used to check whether or not data
 * is a valid email address.
 *
 * @package     Nexista
 * @subpackage  Validators
 */


class Nexista_EmailValidator extends Nexista_Validator
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected $params = array(
        'var' => '' //required - name of flow var to validate
        );

    /**
     * Validator error message
     *
     * @var     string
     */

    protected $message = "is not a valid email";

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
            //$result = preg_match('~^([a-z0-9_]|\-|\.)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,4}$~', $data);
            $this->result = preg_match('~^.+@.+\..{2,4}$~', $data);
            return true;
        }
        $this->setEmpty();
        return true;
    }

} //end class
?>