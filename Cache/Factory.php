<?php

namespace Edify\Cache;

/**
 * A Cache library that will allow you to differenticate the type of information
 * that you are caching.  90+% of content on most pages can be cached.  Caching
 * allows the webserver to serve more pages per second as we release the CPU
 * quicker.  This cache library also knows about Langauge so that it can tell
 * which cached file to load for index.html based on the language of the site.
 *
 * This library will cache the following types of content.
 *
 * - pages - pages are normally cached for a period of time default is 5 minutes.
 *
 * - static - cached forever or until something changes it.  The web page will
 *   not generate these files they are produced by back end systems.  which
 *   could be on the same server or another server and rSynced to the webserver
 *
 * @example  $status = Cache::checkPage("index.html",300);
 * @licence http://php-programmers/licences/Freetard FreeTard licence
 * @author IrishAdo <irishado@php-programmers.net>
 */
class Factory {

    /**
     * define the constant DYNAMIC so that Intellisense will pick up the string.
     */
    Const DYNAMIC = "\\Edify\\Cache\\Dynamic";
    /**
     * define the constant STATICS so that Intellisense will pick up the string.
     */
    Const STATICS = "\\Edify\\Cache\\Statics";
    
    private $driver = null;
    private $language = 'EN';
    private $path = null;
    /**
     * Factory constructor you pass the NameSpace/ClassName as the driver and
     * the factory will initialise that class.  If you use a namespace outside
     * of Edify then dont forget if you are using the Utils::Loader class that
     * you will need to register the vendor.
     *
     * @param String $driver the driver is the namespace that
     * @param String $cachePath the location of the cache root path
     * @param String $language What language is the page in defaults to EN for english
     * @author IrishAdo <irishado@php-programmers.net>
     */
    public function __construct($driver, $cachePath, $cachingLanguage = 'EN') {

        // create a new driver so thet the load and save functions will use the
        // drivers load and save functions they in turn will eventually call the
        // factory saveFile and loadFile functions

        // we dont need to check if the file exists as the Utils Loader will do
        // that for us.  If you dont use the Utils::Loader class then you will
        // have to include the class files yoru self
        $this->driver = new $driver($this);
        $this->path = $cachePath;
        $this->language = $cachingLanguage;
    }
    
    /**
     * Load information from the supplied driver
     *
     * Parameters are an array the specification of the array structure are
     * specified in each driver.
     *
     * @param Array Parameters
     * @return Mixed See Driver for details.
     */
    function load($parameters){
        return $this->driver->load($parameters);
    }

    /**
     * function to load the content from a file.
     * @param String $path the path to the projects cache directory
     * @return String
     */
    public function loadFile($path) {
        return file_get_contents($path);
    }

    /**
     * Save information through the supplied driver
     *
     * Parameters are an array the specification of the array structure are
     * specified in each driver.
     *
     * @param Array Parameters
     * @return Mixed See Driver for details.
     */
    function save($parameters){
        return $this->driver->save($parameters);
    }

    /**
     * Save a buffer to a specific file.
     *
     * @param String Path to save file to
     * @param String Buffer to save
     */

    public function saveFile($path, $buffer) {
        $filePointer = fopen($path, "w");
        fwrite($filePointer, $buffer);
        fclose($filePointer);
    }

    /**
     * get the real filepath to load / save  adds the language to the end of the file
     * so that you can use the exact same code to render a copy of the site in a
     * different language.
     *
     * @param String the subdirectory of the cache where the files will be located
     * @param String Unique File Id or URL
     * @return String representing the full real path to the file.
     */
    public function getFileName($type, $UniqueFileId) {
        return realpath($this->path) . "/$type/" . $UniqueFileId . "." . $this->language;
    }

}

?>
