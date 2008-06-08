<?php
/**
 * -File        Alpha.Validator.php
 * -Copyright   Nexista
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
 * @package     Nexista
 * @subpackage  Validators
 * @author      Joshua Savage
 */
 
/**
 * This validator is used to check whether or not data
 * is composed of only letters.
 *
 * @package     Nexista
 * @subpackage  Validators
 */


class Nexista_AlphaValidator extends Nexista_Validator
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

    protected $message = "must be only letters";


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
            $this->result = preg_match('~^\D+$~', $data);
            return true;
        }
        $this->setEmpty();
        return true;
    }

} //end class
?>