<?php
/*
 * -File        Action.php - Wed Aug 14 14:34:06 MST 2002
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage <>
 * -Author		Albert Lash <>
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage <>
 * @author      Albert Lash <>
 */
 
/**
 * This action applies crypt hash to data
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Crypt_md5Action extends Action
{


    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'pwd' => '', //required - name of flow var to md5
        'salt' => '' //required - name of flow var to md5
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {   
        
		$pwd = Flow::find($this->params['pwd']);
		if($this->params['salt']=='') {
			list($usec, $sec) = explode(' ', microtime());
			$seed = (float) $sec + ((float) $usec * 100000);
			srand($seed);
			$randval = rand();	
			$mysalt=$randval.$randval;
			$mysalt.=$randval.$randval;
			$mysalt.=$randval.$randval;
			$mysalt=substr($mysalt,0,8);
			//echo "$mysalt";
			$mysalt = "$1$".$mysalt."$";
		} else { 
			$salt = Flow::find($this->params['salt']);
			$mysalt = $salt->item(0)->nodeValue;
        }
		if($pwd->length === 1)
        {            
			if (CRYPT_MD5 == 1) {
			$mypwd = $pwd->item(0)->nodeValue;
			$crypt = crypt($mypwd, $mysalt);
			Flow::add("cryptmd5", $crypt);
            return true;
			}
        }
         
        return false;
            

    }
} //end class

?>
