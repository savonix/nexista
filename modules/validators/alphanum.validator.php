<?php
/*
 * -File        alphanum.validator.php
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
 * is composed of only alphanumeric characters.
 *
 * @package     Nexista
 * @subpackage  Validators
 */


class Nexista_AlphanumValidator extends Nexista_Validator
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected $params = array(
        'var' => '' //required - name of flow var to regexp
        );


    /**
     * Validator error message
     *
     * @var     string
     */

    protected $message = "must be alphanumeric characters only";


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
            $this->result = preg_match('~^\w*$~m', $data);          //^(.+|[^.]+)+$
            return true;
        }

        $this->setEmpty();
        return true;
    }

} //end class
?>