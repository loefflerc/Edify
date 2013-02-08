<?php

/** The Loader class which is part of the Edify\Utils namespace
 * 
 */

namespace Edify\Utils;
use Exception;

/** Define the Loader class that allows autoloading of classes with out have to
 * include each and every class on every script.
 * 
 * This allows you to not worry about including a class as long as the class file 
 * follows the Standard for namespaces <vender>/path/of/class.php
 * 
 * @author IrishAdo <irishado@php-programmers.net>
 * @licence Freetard - do what you wish technology, give back, keep, sell up to you.
 */
class Loader {

    // define a instance variable
    /**
     *
     * @var SELF the current instance
     */
    private static $instance;

    /**
     * @static
     * @var Array list of vendors that the autoloader knows about.
     */
    private static $vendors;

    /** initalise the class and add the Path to this Library Vendor
     * 
     * @author IrishAdo <irishado@php-programmers.net>
     */
    public static function init() {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }
        
        self::registerVendor('Edify', dirname(dirname(__FILE__)));
        return self::$instance;
    }

    /** Constructor which sets the loader function to be an auto loader
     *
     * @author IrishAdo <irishado@php-programmers.net>
     */
    public function __construct() {
        spl_autoload_register(array($this, "auto"));
    }

    /** Preprocessor function which will attempt to include a file contains a class before the new command executes
     * 
     * @author IrishAdo <irishado@php-programmers.net>
     * @param Class name 
     */
    public function auto($class) {
        // split the namespace and class up into parts
        $parts = explode("\\", $class);

        // the first part of the namespace is the vendor a unique project identifier
        $choosenVendor = $parts[0];

        // remove the end and the beginning of the path
        unset($parts[0]);

        $path = implode("/", $parts);

        // if we have not listed the path for that vendor then we don't know where it is so die.
        if (!isset(self::$vendors[$choosenVendor])) {
            error_log("[Edify\Utils\Loader] Vendor $choosenVendor not found use \Edify\Utils\Loader::AddVendorPath(\$vendor,\$path);");
            throw new Exception('Sorry we could not find a class located at '. $checkPathforFile);
        } else {

            // work out the path to look for the file based on the Vendors path.
            $checkPathforFile = realpath(self::$vendors[$choosenVendor]) . "/" . $path . ".php";

            // if the file does exist then include it or die.
            if (file_exists($checkPathforFile)) {
                require_once($checkPathforFile);
            } else {
                error_log("[Edify\Utils\Loader] We could not find the following file in the specified vendor -> $checkPathforFile");
                throw new Exception('Sorry we could not find a class located at  '. $checkPathforFile);
            }
        }
    }

    /** function to register a vendor to the Auto loader so that it knows where
     * subdirectory the files for that vendor is located.
     *
     * For example if I created a new application called Mapper then the
     * Namespace for the elements in Mapper should be Mapper\*  by registering
     * the vendor "Mapper" we can look in the correct location for all of the
     * Classes for that namespace.  This allows developers to have many different
     * namespaces and for the Autoloader functionality to know where to go for each.
     *
     * @author IrishAdo <irishado@php-programmers.net>
     * @param String the name of the Vendor
     * @param String Path to look for the class file.
     * @example self::AddVendorPath('Edify', PP_NET_CL_PATH);
     */
    public static function registerVendor($vendor, $path) {
        self::$vendors[$vendor] = $path;
    }

}

\Edify\Utils\Loader::init();
?>