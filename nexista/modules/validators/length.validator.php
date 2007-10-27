<?php
/*
 * -File        $Id: length.validator.php,v 1.1 2005/04/29 01:49:53 amadeus Exp $
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
 * This validator is used to check that a string is under a certain length.
 *
 * @package     Nexista
 * @subpackage  Validators
 */


class LengthValidator extends Validator
{


    /**
     * Function parameter array
     *
     * @var     array
     */

    protected $params = array(
        'var' => '', //required - name of flow var to validate
        'length' => '' //required - name of flow var to validate
        );

    /**
     * Validator error message
     *
     * @var     string
     */

    protected $message = "is too long";


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
            $this->result = strlen($data) <= (int)($this->params['length']);
            return true;
        }
        $this->setEmpty();
        return true;

    }

} //end class
?>
