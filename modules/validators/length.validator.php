<?php
/**
 * -File        Length.Validator.php
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
 * This validator is used to check that a string is under a certain length.
 *
 * @package     Nexista
 * @subpackage  Validators
 */

class Nexista_LengthValidator extends Nexista_Validator
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
        $data = Nexista_Path::get($this->params['var'], 'flow');
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