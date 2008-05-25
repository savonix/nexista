<?php
/*
 * -File        dummy.validator.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      joshua savage
 */

/**
 * @package     Nexista
 * @subpackage  Validators
 * @author      Joshua Savage
 */
 
/**
 * This validator is just a dummy validator. It is used when a field is required
 * but no other validation is needed.
 *
 * @package     Nexista
 * @subpackage  Validators
 */

class Nexista_DummyValidator extends Nexista_Validator
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected $params = array(
        'var' => '' //required - name of flow var to validate
        );

    /**
     * Validator error message
     *
     * @var     string
     */

    protected $message = "is required";

    /**
     * Applies validator
     *
     * @return  boolean     success
     */

    public function main()
    {
        $data = Nexista_Path::get($this->params['var'], 'flow');

        if(!empty($data)  || $data === '0')
        {
            $this->result = true;
            return true;
        }

        $this->setEmpty();
        return true;
    }

} //end class
?>