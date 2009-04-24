<?php
/**
 * -File        Phone.Validator.php
 * -Copyright   Savonix Corporation
 * -Author      Joshua Savage
 * -Author      Albert Lash
 *
 * PHP version 5
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

/**
 * This validator is used to check whether or not data
 * is a valid phone number
 *
 * @package     Nexista
 * @subpackage  Validators
 */

class Nexista_PhoneValidator extends Nexista_Validator
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

    protected $message = "must be a valid phone number";


    /**
     * Applies validator
     *
     * @return  boolean success
     */

    public function main()
    {
        $data = Nexista_Path::get($this->params['var'], 'flow');

        if(!empty($data))
        {
            $data = eregi_replace("(\(|\)|\-|\+|\.)","",$data);

            $this->result = preg_match('~^\d{7,13}$~', $data);
            return true;
        }
        $this->setEmpty();
        return true;
    }

} //end class
?>