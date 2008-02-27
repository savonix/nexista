<?php
/*
 * -File        gd.validator.php
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
 * This validator checks whether the format of the given image is supported
 * by our version of GD.
 * This needs the mime type of image passed. It is available in the request
 * var $_FILES['userfile']['type'] which can be passed as flow var:
 * flow://_files/file/type
 *
 * @package     Nexista
 * @subpackage  Validators
 */

class Nexista_GdValidator extends Nexista_Validator
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected $params = array(
        'var' => '' //required - mime type of image
        );

    /**
     * Validator error message
     *
     * @var     string
     */

    protected $message = "";  //dealt with below


    /**
     * Applies validator
     *
     * @return  boolean     success
     */

    public function main()
    {


        include_once(NX_PATH_LIB.'gd/gdimage.php');

        $gd = new GdImage();

        $this->message = 'is an invalid image type. supported types are: .'.implode($gd->getSupportedTypes(),', .');

        $data = Nexista_Path::get($this->params['var'], 'flow');

        if(!empty($data))
        {
            $this->result = $gd->isSupported($data);
            return true;
        }

        $this->setEmpty();
        return true;

    }

} //end class
?>