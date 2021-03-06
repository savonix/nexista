<?php
/**
 * -File        Value.Validator.php
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
 * This  validator checks that the data is equal to a certain value (i.e. true)
 * using case insensitve string comparison
 *
 * @package     Nexista
 * @subpackage  Validators
 */


class Nexista_ValueValidator extends Nexista_Validator
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected $params = array(
        'var' => '',    //required - name of flow var to regexp
        'value' => ''   //required - regexp to apply
        );

    
    /**
     * Validator error message
     *
     * @var     string
     */

    protected $message = "is not acceptable";


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
            $this->result = !strcasecmp($data, $this->params['value']);
            return true;            
        }        
        $this->setEmpty();
        return true;
    }

} //end class
?>