<?php

namespace Edify\Cache;

/**
 * The Dynamic driver will check the age of a file before returning the buffer
 * if the file is older than the specified age then the load function will return
 * a NULL
 *
 * @licence FreeTard
 * @author IrishAdo <irishado@php-programmers.net>
 */
class Dynamic{

    private $parentFactory = null;

    /**
     * The constructor for a driver for the cache factory.
     *
     * @param Edify\Cache\Factory $parentFactory
     */
    function __construct($parentFactory){
        $this->parentFactory = $parentFactory;
    }

    /**
     * Request a file from the dynamic cache.
     *
     * Parameters = Array(
     *     "url" => String that represents the URL that was cached,
     *     "age" => Max age in seconds
     * );
     *
     * @param Array The List of Parameters
     */
    public function load($parameters) {
        if (!isset($parameters["url"]) || !is_string($parameters["url"])){
            throw new \InvalidArgumentException("Invalid parameters url incorrect format");
        }
        if (!isset($parameters["age"]) || !is_int($parameters["age"])){
            throw new \InvalidArgumentException("Invalid parameters age incorrect format");
        }
        $url = $this->parentFactory->getFileName(__CLASS__, $parameters["url"]);
        $ageInSeconds = $parameters["age"];

        if (!file_exists($url)){
            return null;
        }
        $now = new \DateTime();


        if ($now->getTimestamp() - filemtime($url) < $ageInSeconds) {
			return $this->parentFactory->loadFile($url);
		} else {
			return NULL;
		}
    }

    /** save a block of dynamic content to the cache.
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
        \Edify\Utils\Log::debugLog("[Edify\Cache\Dynamic]", $url);
        $this->parentFactory->saveFile($this->parentFactory->getFileName(__CLASS__, $url), $buffer);
    }

}

?>
