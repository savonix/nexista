<?php
/*
 * -File        gdimage.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage
 */

/**
 * Some basic image manipulation functions using gd.
 * Mostly thumbnail generation functions at this point.
 *
 */

class GdImage
{

    /**
     * Thumbnail name (full path)
     *
     * @var     string
     */
     
    public $thumbName;
    
    
    /**
     * Resize scale (%)
     *
     * @var     integer 
     */
     
    public $scale;
    
    
    /**
     * image name (full path) of pic to resize
     *
     * @var     string  
     */
     
    public $imageName;
    
    
    /**
     * image mime type
     *
     * @var     string  
     */
     
    public $imageMime;
    

    /**
     * image type of pic to resize
     *
     * @var     string
     */

    public $imageType;


    /**
     * image extension
     *
     * @var     string
     */

    public $imageExt;

    
    /**
     * image width of pic to resize
     *
     * @var     integer width in pixels
     */

    public $imageWidth;


    /**
     * image extension bits
     *
     * @var     array
     */

    private $imageBits = array(IMG_GIF,IMG_JPG,IMG_PNG,IMG_WBMP);
    
    /**
     * image extension strings
     *
     * @var     integer width in pixels
     */

    public $imageExtensions = array('.gif', '.jpg', '.png', '.swf', '.psd', 'bmp');


    /**
     * image height of pic to resize
     *
     * @var     integer 
     */
     
    public $imageHeight;

    /**
     * new thumbnail file prefix
     *
     * @var     string 
     */
     
    public $prefix;
    
  
    /**
     * Creates thumb based on a precentage scale of the original
     *
     * @param       string $image - The full path to an image to resize
     * @param       string $scale - The percentage to scale the image by
     */
     
    public function resizeImageScale($scale)
    {           
        $this->resizeImage($this->imageWidth / $scale, $this->imageHeight / $scale);        
    }

    /**
     * Flushes variables
    */

    public function flushData()
    {
        //TODO probably more. May be move image unlink to this
        $this->thumbName = false;

    }


    /**
     * Creates a thumbnail based on width/heigth maximum constraints
     *
     * This method creates a thumbnail from an image based on given
     * maximum dimension constraints. If the original is smaller, then the 
     * thumbnail will be the same size as the original
     *
     * @param   string      The full path to an image to resize
     * @param   string      The percentage to scale the image by
     */

    public function resizeImageRatio($max_width, $max_height)
    {

    
        $wratio = $this->imageWidth / $max_width;
        $hratio = $this->imageHeight / $max_height;

        //see if original is smaller then size to begin with
        if($wratio > 1)
        {
            //nope
            if( $wratio < $hratio )
            {
                $dest_width = $this->imageWidth / $hratio;
                $dest_height = $max_height;
            }
            else
            {
                $dest_width = $max_width;
                $dest_height = $this->imageHeight / $wratio;
            }
            $this->resizeImage($dest_width, $dest_height);
        }
        //yes - make duplicate then. no resizing
        else
        {
            $this->resizeImage($this->imageWidth, $this->imageHeight);
        }
    }

    
    /**
     * Resizes an image based on exact width/heigth parameters
     *
     * @param   string      The full path to an image to resize
     * @param   string      The percentage to scale the image by
     */
    public function resizeImage($width, $height)
    {

        //check if type is supported by your gd build
        switch($this->imageType)
        {
            
            case(1): //gif
                $imageFunction  = 'imagegif';
                $imageCreateFunction = 'imagecreatefromgif';
               
            break;

            case(2): //jpg
                $imageFunction  = 'imagejpeg';
                $imageCreateFunction = 'imagecreatefromjpeg';
                
            break;

            case(3): //png
                $imageFunction  = 'imagepng';
                $imageCreateFunction = 'imagecreatefrompng';
              
                break;
        }

        //see if a thumb name was given - we create one otherwise
        if(empty($this->thumbName))
            $this->getThumbName();

        // make the thumb
        $thumbnail  = ImageCreateTrueColor($width, $height);
        $color_white = imagecolorallocate($thumbnail, 255, 255, 255);
        imagefill($thumbnail, 0, 0, $color_white);
        
        $new_image = $imageCreateFunction($this->imageName);
                
        imagecopyresampled($thumbnail, $new_image, 0, 0, 0, 0, $width, $height, $this->imageWidth, $this->imageHeight);
        $imageFunction($thumbnail, $this->thumbName, 85);
        imagedestroy($thumbnail);

        // write the new thumb to file
        $thumbdata = fread(fopen($this->thumbName, "r"), filesize($this->thumbName));
        
        unlink($this->thumbName);
        $fp = fopen($this->thumbName, "w");
        fwrite($fp, $thumbdata, 1000000);
        fclose($fp);


        return true;

    }


    /**
     * Determine image type based on name
     *
     *
     * @param   string      The full path to an image to resize
     * @param   string      The  type of the image
     * @param   string      The percentage to scale the image by
     * @return  boolean     success
     */

    public function getImageInfo($image)
    {
    
        //get image info
        $info = getimagesize($image);

        if(!(imagetypes() & $this->imageBits[$info[2]-1]))
        {
            Error::init('Image type not supported by this GD library build.' , NX_ERROR_FATAL);
        }

        $this->imageName = $image;
        $this->imageWidth = $info[0];
        $this->imageHeight = $info[1];
        $this->imageType = $info[2];
        $this->imageExt = $this->imageExtensions[$info[2]-1];
        $this->imageMime = image_type_to_mime_type($this->imageType);


        return true;
    }


    /**
     * Checks if an image type is supported
     *
     *
     * @param   string      The full path to an image
     * @return  boolean     success
     */

    public function isSupported($image)
    {
        //get image info
        $info = getimagesize($image);

        if(!(imagetypes() & $this->imageBits[$info[2]-1]))
        {
            return false;
        }
        return true;
    }


    /**
     * Return supported image types as a array
     *
     * @return  boolean     success
     */

    public function getSupportedTypes()
    {
        $supported = imagetypes();

        $text = array('Gif', 'Jpg', 'Png', 'Bmp');
        $tmp = array();
        for($i = 0; $i < count($this->imageBits); $i++)
        {
            if($supported & $this->imageBits[$i])
                $tmp[] = $text[$i];
        }

        return $tmp;
    }


    /**
     * Creates a name for the thumbnail 
     */
    
    public function getThumbName()
    {
    
        $path = pathinfo($this->imageName);
		$prefix = $this->prefix;
		if ($prefix) { 
			$this->thumbName = $path['dirname'].'/'.$prefix.$path['basename'];
		} else { 
			echo "didn't work!";
			exit;
			//$this->thumbName = $path['dirname'].'/thumb_'.$path['basename'];
		}
    }
    /**
     * Sets prefix for the thumbnail 
     */
    
    public function setThumbPrefix($prefix)
    {
        $this->prefix = $prefix;

		return true;
    }
    
}   // end class
?>