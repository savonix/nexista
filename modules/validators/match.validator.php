<?php
/*
 * -File        match.validator.php
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
 * This validator is used to check whether or not
 * two values are the same.
 *
 * @package     Nexista
 * @subpackage  Validators
 */

class Nexista_MatchValidator extends Nexista_Validator
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected $params = array(
        'var' => '',    //required - name of flow var to validate
        'match' => ''   //required - name of flow var to match to 'var'
        );


    /**
     * Validator error message
     *
     * @var     string
     */

    protected $message = "does not match";


    /**
     * Applies validator
     *
     * @return  boolean     success
     */

    public function main()
    {
        $var = Nexista_Path::get($this->params['var'], 'flow');

        if(empty($var))
        {
            $this->setEmpty();
        }
        $match = Nexista_Path::get($this->params['match'], 'flow');

        if(!strcmp($match, $var))
        {
            $this->result = true;
            return true;
        }
        $this->result = false;
        return true;

    }

} //end class
?>