<?php
/**
 * -File        Builder.php
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
 * This class provides abstract functionality to build the cached php gate files
 * for each action based on the sitemap.
 *
 * It should be extended for each possible tags in the sitemap. In your class
 * extend the getCodeStart() and getCodeEnd() functions.
 * The attributes of the tag are available in the $params array
 *
 * If your tag does not allow nesting of other tag (such as validate, if) then
 * you can disregard the getCodeEnd() method
 *.
 * You should also add any required files into the $required array
 * by extending it and setting new values in your class.
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */


class Nexista_Builder
{

    /**
     * Holds the tag attributes from sitemap
     *
     * @var array
     */

    public $params = array();


    /**
     * Reference to current DOMElement action tag object in current gate
     *
     * @var DOMElement
     */

    public $action;


    /**
     * Determines if we allow regex in some attributes based on gate match
     *
     * @var string
     */

    public $matchType;


    /**
     * Returns array of required files to insert in require_once fields
     *
     * This method should be extended to return an array of all files necessary
     * for this tag to run.
     * Each file will be called in a require_once field on top of gate code.
     *
     * Example:
     * <code>
     * $req[] = NX_PATH_HANDLERS.'script.handler.php';
     * return $req;
     * </code>
     *
     * @return array required files
     */

    public function getRequired()
    {
        return array();
    }

    /**
     * Returns start code for this tag.
     *
     * This is the main function used to write tag code. It either holds
     * the entire snippet in case of a self closing tag (query,xsl)
     * or the start code for nesting tags (if,validate)
     *
     * @return string tag code to insert in gate
     */

    public function getCodeStart()
    {
        return '';
    }


    /**
     * Returns end code for this tag.
     *
     * This method returns ending/closing code for nesting tags
     * such as if,validate. If the tag is self closing, this method does
     * not need to be extended.
     *
     * @return string tag code to insert in gate
     */

    public function getCodeEnd()
    {
        return '';
    }


    /**
     * Inserts whatever exception handling is desired at build time
     *
     * This allows any code in the getCode() class Nexista_to use error handling
     * without worrying about future changes (such as no error handling)
     * The relevant code in the getCode() method would be written as
     *
     * $this->addErrorHandling('someaction()', Ooops', 'NX_ERROR_FATAL');
     *
     * This will result in something like:
     * <code>if (!someaction()) {
     *     Nexista_Error::init('Ooops', NX_ERROR_FATAL);
     * }</code>
     *
     * @param string $content      function to insert in error handler
     * @param string $message      message for error handler - optional
     * @param string $code         valid error code IN QUOTES ie NX_ERROR_FATAL
     * @param mixed  $errorHandler valid php callback to deal with exception
     *
     * @return string error ready code
     */

    public function addErrorHandler($content = '', $message = '',
        $code = 'NX_ERROR_FATAL', $errorHandler = null)
    {
        return $content.';';
        $code[] = 'if (!'.$content.') {';
        $code[] = "Nexista_Error::init('".$message."',
            '".$code."',
            '".$errorHandler."');";
        $code[] = '}';
        return implode(NX_BUILDER_LINEBREAK, $code);

    }


    /**
     * Resets class attributes between tags
     *
     * @return null
     */

    public function reset()
    {
        $this->params = array();
        $this->state  = null;
        $this->action = null;

    }

}

?>