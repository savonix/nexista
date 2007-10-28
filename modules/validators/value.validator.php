<?php
/*
 * -File        value.validator.php
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
 * This  validator checks that the data is equal to a certain value (i.e. true)
 * using case insensitve string comparison
 *
 * @package     Nexista
 * @subpackage  Validators
 */


class ValueValidator extends Validator
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
   
        $data = Path::get($this->params['var'], 'flow');
       
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