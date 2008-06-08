<?php
/**
 * -File        Validator.Handler.php
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
 * This classes provides functionality to validate data fields
 * from Flow using any criteria. Validation criterias (besides checking
 * if required) is provided by add-on modules in the Validators folder.
 *
 * @package     Nexista
 * @subpackage  Handlers
 */

class Nexista_ValidatorHandler
{

    /**
     * Accepts an xml file list of items and validates them according
     * to passed criteria.
     *
     * This function will check a number of fields based on criterias
     * passed along with each, such as required, validation type, etc..
     * It will return false if any fails and detailed results including
     * custom error messages are placed in a ValidationHandlerData object,
     * parts of which will be rendered into xmlStream for access from xsl
     *
     *
     * @param   string      the name of the xml data file
     * @param   boolean     (referece) result
     * @return  boolean     success
     */

    public function process($src, &$result)
    {
        //create a validator data object to hold procedure result
        require_once(NX_PATH_HANDLERS . 'validatorhandlerdata.php');
        $validatorData = new Nexista_ValidatorHandlerData();

        //load validator file
        $xml = simplexml_load_file($src);

        //get the validator name as specified in xml file. this is used to name array in flow
        $validator_name = (string)$xml['name'];
        if (empty($validator_name))
            $validator_name = 'validator';


        //load base validator class
        require_once(NX_PATH_CORE . "validator.php");

        foreach ($xml->children() as $param)
        {
            //get the name of variable to set with good/bad result
            $result_name = (string)$param['name'];

            //process validators for this item
            foreach ($param->children() as $val) {

                $result = true;

                //get type of validator
                $type = (string)$val['type'];

                $required = (string)$val['required'];
                if (empty($required))
                    $required = 'false';

                //and its parameters
                $args = preg_split('~(\040)*,(\040)*~',(string)$val['params']);

                //load the validator module file based on $type
                require_once(NX_PATH_VALIDATORS . trim(strtolower($type)) . ".validator.php");

                //build the class name to load
                $class = 'Nexista_'.trim(ucfirst($type)) . "Validator";
                $validator =& new $class();

                if (!$validator->process($args, $required, $result)) {
                    return false;
                }

                //get any message from the validator
                if ($validator->isEmpty() && $validator->isRequired()) {
                    $text = $validator->getMessage();
                } else {
                    $text = (string)$val['text'];
                    //if no custom message we use default
                    if (empty($text))
                        $text = $validator->getMessage();
                }

                if (!$result) {
                    $validatorData->itemFail($result_name,
                        trim(strtolower($type)), $text);
                }

                unset($validator);
            }

            $result_text = (string)$param['text'];

            if (!empty($result_text) &&
                isset($validatorData->validatorData[$result_name])) {
                $validatorData->itemMessage($result_name, $result_text);
            }
        }

        if ((string)$xml['debug'] === 'true') {
            Nexista_Debug::dump($validatorData->validatorData,
                $validator_name .' (validation data) ');
        }

        $result = $validatorData->getResult(); //1 = valid data


        if (!$result) {
            //assign validator data to Flow
            Nexista_Flow::add($validator_name,$validatorData->validatorData);
        }

        //clean up
        unset($validatorData);

        return true;
    }

} // end class

?>