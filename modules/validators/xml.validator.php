<?php
/*
 * -File        $Id: xml.validator.php,v 1.3 2005/04/29 01:49:31 amadeus Exp $
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
 * This validator is used to make sure that a string
 * contains is xml safe (i.e all tags close and nest appropriately)
 * It does not check for headers and root tags.
 *
 * @package     Nexista
 * @subpackage  Validators
 */


class XmlValidator extends Validator
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
        $data = Path::get($this->params['var'], 'flow');
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
