<?php
/**
 * -File        Xml.Validator.php
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
 * This validator is used to make sure that a string
 * contains is xml safe (i.e all tags close and nest appropriately)
 * It does not check for headers and root tags.
 *
 * @package     Nexista
 * @subpackage  Validators
 */


class Nexista_Nexista_XmlValidator extends Nexista_Validator
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

    protected $message = "must be valid xml";


    /**
     * Applies validator
     *
     * @return  boolean success
     */

    public function main()
    {
        $data = Nexista_Path::get($this->params['var'], 'flow');
        //see if there is a declaration
        if(!strstr($data, '<?xml'))
        {
            //no. let's add one as well
            //we also add root tags anyways whether they are there or not since all we need is basic validation
            $data = '<?xml version="1.0"?><xml>'.$data.'</xml>';
        }

        $this->result = simplexml_load_string($data);
        return true;
        

    }

} //end class
?>