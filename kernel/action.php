<?php
/*
 * -File        action.php 
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2004-2007, Nexista
 * -Author      joshua savage
 * -Author      albert lash 
 */

/**
 * @package Nexista
 * @author Joshua Savage 
 */
 
/**
 * This class is the base class upon which to extend custom actions
 * 
 * @tutorial    action.pkg
 * @package     Nexista
 */
 

class Action
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
     * @param   array       class parameters
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
     * Loads class parameters
     *
     * This function will check if the required parameters
     * for this class are supplied and will load them into
     * $this->params array
     *
     * @param   array       class parameters
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
                    Error::init('Class '. get_class($this).' does not have the required number of parameters', NX_ERROR_FATAL);
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
