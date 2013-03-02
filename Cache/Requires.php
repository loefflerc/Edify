<?php

namespace Edify\Cache;

/**
 * The Requires driver extends the same functionality as the STATICS class it
 * will only overwrite the load function as it will require the file instead of
 * putting it in a buffer.
 *
 * @licence FreeTard
 * @author IrishAdo <irishado@php-programmers.net>
 */
class Requires {

    private $parentFactory = null;

    /**
     * The constructor for a statics driver for the cache factory.  Statics are
     * considered to be things that only change with user interaction like
     * adding a new menu to a system the menu would be cached as static.
     * Loading the static cache is quicker than the dynamic cache as the static
     * will not check the age of a file.
     *
     * @param Edify\Cache\Factory $parentFactory
     */
    function __construct($parentFactory){
        $this->parentFactory = $parentFactory;
    }

    /**
     * load a list of static content that we do not care about its age.
     *
     * The static content could be produced either by this site or another site
     * we just server the content when asked  for example static content might
     * be the Header/Footer/Menus each split up into a seperate buffer that we
     * can load with out having to process the steps to build each time we load
     * a page.
     *
     * Parameters = Array(
     *     "list" => List of Strings that represent static buffers to load
     * );
     *
     * @param Array The List of Parameters
     */
    function load($parameters) {
        if (!isset($parameters["url"])) {
            throw new \InvalidArgumentException("Invalid parameters url incorrect format");
        }
        $url = $this->parentFactory->getFileName("Requires", $parameters["url"]);
        if (!file_exists($url)) {
            return false;
        }
        require($url);
        return $object;
    }

    /** save a block of static content to the cache.
     *
     * @param String $UniqueFileId the URL of the page to load
     * @param String $buffer the buffer to save
     * @author IrishAdo <irishado@php-programmers.net>
     */

    public function save($parameters) {
        if (!isset($parameters["url"]) || !is_string($parameters["url"])){
            throw new \InvalidArgumentException("Invalid parameters url incorrect format");
        }
        if (!isset($parameters["buffer"]) || !is_string($parameters["buffer"])){
            throw new \InvalidArgumentException("Invalid parameters buffer incorrect format");
        }
        $url = $this->parentFactory->getFileName("Requires", $parameters["url"]);
        $this->parentFactory->saveFile($url, $parameters["buffer"]);
    }
}

?>
