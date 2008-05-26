<?php
/*
 * -File        action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      joshua savage
 * -Author      Albert Lash
 */

/**
 * @package Nexista
 * @author Joshua Savage
 * @author Albert Lash
 */
 
/**
 * This class Nexista_is the base class Nexista_upon which to extend custom actions
 * 
 * @package     Nexista
 */

class Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array();


    /**
     * Loads parameters and applies action
     *
     * @param   array       class Nexista_parameters
     * @return  boolean     success
     */

    public function process(&$params)
    {

        if(!$this->applyParams($params))
        {
            return false;
        }

        return $this->main();
    }


    /**
     * Applies action
     *
     * @return  boolean     success
     */

    protected function main()
    {

        return true;

    }


    /**
     * Loads class Nexista_parameters
     *
     * This function will check if the required parameters
     * for this class Nexista_are supplied and will load them into
     * $this->params array
     *
     * @param   array       class Nexista_parameters
     * @return  boolean     success
     */

    protected function applyParams(&$params)
    {
        $cnt = 0;
        foreach($this->params as $key => $val)
        {
            if(empty($params[$cnt]))
            {
                if($val == 'required')
                {
                    Nexista_Error::init('Class '. get_class($this).' does not have the required number of parameters', NX_ERROR_FATAL);
                }
                $this->params[$key] = false;
            }
            else
            {
                $this->params[$key] = $params[$cnt];
            }
            $cnt++;

        }
        return true;
    }

} //end class

?>