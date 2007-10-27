<?php
/*
 * -File        fileuploadaction.php - Wed Aug 14 14:34:06 MST 2002
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage <>
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage <>
 */
 
/**
 * This action moves an uploaded image from /tmp to
 * the desired location (default is Nexista tmp - NX_PATH_TMP)
 * and creates a thumbnail of the image. It will also, if no name is provided,
 * rename the file using a 10 digit random number with the same extension.
 * This can be useful in some scenarios when an image may get replaced but the 
 * filename is used in some article/pages and therefore needs to  stay the same.
 * This also prevents dealing with images with spaces in their name and other ^#&*$
 * 
 * 
 * 
 * @package     Nexista
 * @subpackage  Actions
 */

class imageAction extends Action
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'file' => '',   //required - name of $FILES array index for file in question
        'dest' => '',   //optional - destination path/filename from NX_PATH_APP to move file to
        'name' => ''    //optionam filenam to rename file with
        );


    /**
     * Applies action
     *
     * @return  boolean     success
     */

    protected  function main()
    {
    
        //thumb size
        $maxwidth = 150;
        $maxheight = 150;
        

        
        
        //see if a path is given. we default to Nexista temp dir
        $dest = empty($this->params['dest']) ? NX_PATH_TMP : trim($this->params['dest'],'/').'/';
        
        if(!empty($this->params['name']))
        {
            $name = Path::get($this->params['name']);
        }
        else
        {
            //TODO change this to a better extension grabbing
            $name =  rand(1000000000, 9999999999).substr($_FILES[$this->params['file']]['name'], -4);
        }


        if(!move_uploaded_file($_FILES[$this->params['file']]['tmp_name'], $dest.$name))
        {
            Error::init('FileUpload action was unable to move uploaded file: '.$_FILES[$this->params['file']]['name'] . '. Check '.$dest.' permissions', NX_ERROR_WARNING);
            return false;
        }

        //assign full destination path to flow as it can be useful
        $res = Flow::find('//_files/file');
        if($res->length === 1)
        {
            Flow::add('new_dir', $dest,$res->item(0));
            Flow::add('new_name', $name,$res->item(0));
           

        }
        //get decent permissions on this
        chmod($dest.$name,0644);
        
        // make a new gd image handler object
        require_once(NX_PATH_LIB.'/gd/gdimage.php');
        $gd =& new GdImage();

        
        //get image info
        $gd->getImageInfo($dest.$name);
        
        //set path for thumb
        $gd->thumbName = $dest.'thumbs/'.$name;
        //make thumb
        $gd->resizeImageRatio($maxwidth, $maxheight);
        
        return true;
    }




} //end class

?>
