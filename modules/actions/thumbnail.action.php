<?php
/*
 * -File        thumbnailaction.php - Wed Aug 14 14:34:06 MST 2002
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
 * This action makes a thumbnail image from the given image path.
 * It accepts maximum dimensions to scale image to
 *
 * @package     Nexista
 * @subpackage  Actions
 */
 

class ThumbnailAction extends Action
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'file' => '',       //required - name of file to thumb
        'maxwidth' => '',   //optional - maximum width to scale image to
        'maxheight' => '',   //optional - maximum height to scale image to
	'prefix' => ''
        );


    /**
     * Default thumbnail width (pixels)
     *
     * @var     integer
     */

    private $defaultWidth = 150;


    /**
     * Default thumbnail height (pixels)
     *
     * @var     integer
     * @access  private
     */

    private $defaultHeight = 150;


    /**
     * Applies action
     *
     * @return  boolean     success
     */

    protected  function main()
    {
        $file = Path::get($this->params['file'],"flow");
        $maxwidth = empty($this->params['maxwidth']) ?   $this->defaultWidth : $this->params['maxwidth'];
        $maxheight = empty($this->params['maxheight']) ?  $this->defaultHeight : $this->params['maxheight'];
		$prefix = $this->params['prefix'];
		
		if(!is_numeric($maxwidth))
		{
			$maxwidth = Path::get($maxwidth,"flow");
		}
		
		if(!is_numeric($maxheight))
		{
			$maxheight = Path::get($maxheight,"flow");
		}
		
        // make a new gd image handler object
        require_once(NX_PATH_LIB.'/gd/gdimage.php');
        $gd =& new GdImage();

        //get image info        
		$gd->setThumbPrefix($prefix);
        $gd->getImageInfo($file);
		Flow::add('width', $gd->imageWidth);
		Flow::add('height', $gd->imageHeight);

        $gd->resizeImageRatio($maxwidth, $maxheight);


        //TODO standardize flow names
		$thumbfilename=basename($gd->thumbName);
		Flow::add('thumbname', $thumbfilename);
        return true;
    }




} //end class

?>
