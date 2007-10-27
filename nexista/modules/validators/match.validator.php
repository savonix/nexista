<?php
/*
 * -File        $Id: match.validator.php,v 1.3 2005/04/29 01:49:31 amadeus Exp $
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
 * This validator is used to check whether or not
 * two values are the same.
 *
 * @package     Nexista
 * @subpackage  Validators
 */

class MatchValidator extends Validator
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
        $var = Path::get($this->params['var'], 'flow');

        if(empty($var))
        {
            $this->setEmpty();
        }
        $match = Path::get($this->params['match'], 'flow');

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
