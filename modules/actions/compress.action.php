<?php
/*
 * -File        compress.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002-2007, Nexista
 * -Author      joshua savage  
 * -Author		Albert Lash 
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage 
 * @author      Albert Lash
 */
 
/**
 * This action applies gzip compress to data
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class CompressAction extends Action
{


    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'var' => '' //required - name of flow var to gzip
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {   
        
		$res = Flow::find($this->params['var']);
        if($res->length === 1)
        {            
			$text = $res->item(0)->nodeValue;
			$res->item(0)->nodeValue = base64_encode(gzcompress($text,6));
            return true;
        }
         
        return false;
            

    }
} //end class

?>