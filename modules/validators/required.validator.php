<?php
/*
 * -File        $Id: required.validator.php,v 1.3 2005/04/29 01:49:31 amadeus Exp $
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage, 
 */

/**
 * @package     Nexista
 * @subpackage  Validators
 * @author      Joshua Savage <>
 */
 
/**
 * This validator is used to check whether or data
 * is present.
 *
 * @package     Nexista
 * @subpackage  Validators
 */

class RequiredValidator extends Validator
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

    protected $message = "is required";


    /**
     * Applies validator
     *
     * @return  boolean success
     */

    public function main()
    {

        $data = Path::get($this->params['var'], 'flow');

        if(!empty($data))
        {
            $this->result = !empty($data);
            return true;
        }
        $this->setEmpty();
        return true;



    }

} //end class
?>
