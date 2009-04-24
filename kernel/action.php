<?php
/**
 * -File        Action.php
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
 * This class is the base class upon which to extend custom actions
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

class Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array();


    /**
     * Loads parameters and applies action
     *
     * @param array &$params class parameters
     *
     * @return boolean success
     */

    public function process(&$params)
    {

        if (!$this->applyParams($params)) {
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
     * @param array &$params class parameters
     *
     * @return boolean success
     */

    protected function applyParams(&$params)
    {
        $cnt = 0;
        foreach ($this->params as $key => $val) {
            if (empty($params[$cnt])) {
                if ($val == 'required') {
                    Nexista_Error::init('Class '. get_class($this).'
                        does not have the required number of parameters',
                            NX_ERROR_FATAL);
                }
                $this->params[$key] = false;
            } else {
                $this->params[$key] = $params[$cnt];
            }
            $cnt++;

        }
        return true;
    }

} //end class

?>