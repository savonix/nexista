<?php
/**
 * -File        Upload.Action.php
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
 * This action moves an uploaded file from /tmp to
 * the desired location (default is Nexista tmp - NX_PATH_TMP)
 * 
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_uploadAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'file' => '',       //required - name of $FILES array index for file in question
        'dest' => '',		//optional - destination path/filename from NX_PATH_APP to move file to
        'prefix' => ''      //optional - unique id for prefix so that files are not overwritten
        );


    /**
     * Applies action
     *
     * @return  boolean     success
     */

    protected  function main()
    {
        $mode = 0775;
        //see if a path is given. we default to Nexista temp dir
        if(!$dest = Nexista_Path::get($this->params['dest'], 'flow')) {
			$dest = empty($this->params['dest']) ? NX_PATH_TMP : trim($this->params['dest'],'/').'/';
		}
        //websvn: stream wrapper
        if(strpos($dest,"websvn")) {
            require('HTTP/WebDAV/Client.php');
        }

        if(!empty($_FILES[$this->params['file']]['tmp_name']))
        {

            $name = $_FILES[$this->params['file']]['name'];
            $prefix = Nexista_Path::get($this->params['prefix'],"flow");
            if($prefix!='') {
                $unique_id = $prefix;
                $name=$unique_id."_".$name;
            }
            if(!is_dir($dest)) {
                mkdir($dest,$mode,TRUE);
            }
            if(!move_uploaded_file($_FILES[$this->params['file']]['tmp_name'], $dest.$name))
            {
                Nexista_Error::init('Upload action was unable to move uploaded file: '.$name . '. Check '.$dest.' permissions', NX_ERROR_WARNING);
                return false;
            }

            //assign full destination path to flow as it can be useful
            $res = Nexista_Flow::find('//_files/file');
            if($res->length === 1)
            {

                Nexista_Flow::add('new_dir', $dest,$res->item(0));
                Nexista_Flow::add('new_name', $dest.$name,$res->item(0));

            }
            //chmod($dest.$name,0644);
        }
        return true;
    }

} //end class

?>