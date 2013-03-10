<?php

/** The Log class which is part of the Edify\Utils namespace
 * 
 */

namespace Edify\Utils;
if (!defined("ACCESS_LOG_PATH")){
    define("ACCESS_LOG_PATH", "/tmp/site.access.log");
}
/** Define the Log class
 * 
 * This allows you to log information into the error_log file
 * @author IrishAdo <irishado@php-programmers.net>
 * @licence Freetard - do what you wish technology, give back, keep, sell up to you.
 */
class Log {

    /** The level at whcih the debugger will actually write to the error_log
     * @static
     * @var String a list of the handles that are listed to output to the error_log
     */
    private static $debugLevel = "";

    /** the instance of this object so that if you initialise another class of this type it just uses this one.
     * @static
     * @var Self an Instance of this class
     */
    private static $instance;

    /** Initialise the single instance
     * @author IrishAdo <irishado@php-programmers.net>
     */
    public static function init() {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Constructor for this class
     * @author IrishAdo <irishado@php-programmers.net>
     */
    public function __construct() {

    }

    /** Allow the setting of the Debug level this will allow you to specify the error handles that will be reported in the error log.
     * @param String Handle to allow to debug.
     * @author IrishAdo <irishado@php-programmers.net>
     */
    static function setDebugLevel($handleString) {
        self::$debugLevel = $handleString;
    }

    /** add a statement to the error log if the $handle is listed in the debugLevel
     * 
     * @parma String handle to debug
     * @param String statement to write ot the log.
     * @author IrishAdo <irishado@php-programmers.net>
     * @example /Utils/Log::debugLog("Database", $sqlStatement);
     */
    static function debugLog($handle, $statement) {
        if (
        // if debug is ALL then write message to error_log file
                self::$debugLevel == "ALL" ||
                in_array(
                        // strip the square brackets off the handle
                        str_replace(Array("[", "]"), Array("", ""), $handle),
                        // and check it in not in this array of handles
                        explode(",", self::$debugLevel . ",")
                )
        ) {
            // write to the error_log rather than the screen to keep information secure
            error_log("$handle: $statement\n");
        }
    }

    /** add a statement to the error log unconditionaly
     * 
     * @parma String handle to debug
     * @param String statement to write ot the log.
     * @author IrishAdo <irishado@php-programmers.net>
     * @example /Utils/Log::debugError("Database", "Connection failed!");
     */
    static function debugError($handle, $statement) {
        error_log("$handle: $statement\n");
    }

    function access_log($msg) {
        error_log("$msg\r\n", 3, ACCESS_LOG_PATH);
    }

}

// side effect: initialises the class as a singleton.
\Edify\Utils\Log::init();
?>