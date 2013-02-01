<?php

namespace Edify\Cache;

/**
 * The Statics driver will return a list of cached buffers if the files are in
 * the statics cache directory.
 *
 * @licence FreeTard
 * @author IrishAdo <irishado@php-programmers.net>
 */
class Statics {

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
        if (!isset($parameters["list"])){
            throw new \InvalidArgumentException("Invalid parameters list incorrect format");
        }
        $listOfStaticWidgets = $parameters["list"];
        $listOfBuffers = Array();
        foreach ($listOfStaticWidgets as $keyPath) {
            $url = $this->parentFactory->getFileName("statics", $keyPath);
            if (!file_exists($url)){
                $listOfBuffers[$keyPath] = null;
            } else {
                $listOfBuffers[$keyPath] = $this->parentFactory->loadFile($url);
            }
        }
        return $listOfBuffers;
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
        $url = $parameters["url"];
        $buffer = $parameters["buffer"];
        $this->parentFactory->saveFile($this->parentFactory->getFileName("statics", $url), $buffer);
    }

}

?>
