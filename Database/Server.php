<?php

namespace Edify\Database;
use PDO;
/**
 *
 * @licence http://php-programmers/licences/Freetard FreeTard licence
 * @author IrishAdo <irishado@php-programmers.net>
 */
class Server {

    CONST MYSQL = "Edify\Database\Drivers\Mysql";
    CONST MSSQL = "Edify\Database\Drivers\Mssql";
    CONST DBTYPE_TINYINT   = 0;
    CONST DBTYPE_SMALLINT  = 1;
    CONST DBTYPE_INT       = 2;
    CONST DBTYPE_BIGINT    = 3;
    CONST DBTYPE_VARCHAR   = 4;
    CONST DBTYPE_CHAR      = 5;
    CONST DBTYPE_DATETIME  = 6;
    CONST DBTYPE_DECIMAL   = 7;
    CONST DBTYPE_TEXT      = 8;
    CONST DBTYPE_SMALLTEXT = 9;
    CONST DATETIME_SAVEFORMAT = "Y-m-d H:i:s";

    private $driver = null;

    /** Constructor which takes the type of driver we are to use to connect to 
     * the database.  The Driver will check that the desired driver is installed.
     * 
     * @param string $driver
     * @throws \InvalidArgumentException
     */
    function __construct($driver) {
        // create a new driver so that any special functions or code
        // that is unique for that database can be handled correctly
        $this->driver = new $driver($this);
    }

    /**
     * Checks if php is configured to use a PDO driver throws an exception if 
     * the driver is not installed otherwise it return TRUE;
     * 
     * @param String $driverName the string that represents the driver 
     * @return boolean throws an error if driver not available.
     * @throws PDOException if the system does not have the correct driver installed
     */
    function checkForDriver($driverName) {
        if (!in_array($driverName, PDO::getAvailableDrivers())) {
            \Edify\Utils\Log::debugLog("[Edify\Database\Server]", "Driver " . $driverName . " was not found to be installed PDO::getAvailableDrivers()");
            throw new PDOException("The specified driver is not available");
            /// not sure if this would ever get executed but my OCD wants it in.
            return false;
        }
        return true;
    }

    /**
     * Using this Database Driver open a connection to the specified server.
     * 
     * @param type $database
     * @param type $host
     * @param type $username
     * @param type $password
     * @return type
     */
    function getConnection($database, $host, $username, $password) {
        \Edify\Utils\Log::debugLog("[Edify\Database\Server]", "Attempting to connect to a database using driver " . $this->driver->getType());
        $this->driver->getConnection($database, $host, $username, $password);
    }

    /**
     * get the schema structure
     */
    function getSchema() {
        return $this->driver->getSchema();
    }

    /**
     * prepare a SQL statement
     */
    function prepare($sql) {
        return $this->driver->prepare($sql);
    }
    /**
     *
     */
    function execute($parameters = Array()) {
        return $this->driver->execute($parameters);
    }
    function getInsertId(){
        return $this->driver->getInsertId();
    }

    /**
     * convert the string from the show columns functions type column to a
     * \Edify\Database\Server::DBTYPE_* constant.
     *
     * @param String $columnType  the column type from the database extraction
     * @return type
     */
    function examineColumnType($columnType) {
        if (strpos($columnType, "(") === false) {
            return $columnType;
        }
        $matches = Array();

        preg_match("/(.*)\(([0-9,]{1,})\)(.*)/", $columnType, $matches);
        return Array(
            $this->getTypeConstant(trim($matches[1])),
            trim($matches[2]),
            trim($matches[3])
        );
    }

    /**
     * return a constant that represents the column type.
     *
     * @param type $type
     * @return Mixed return the constant that represents the database column type.
     */
    function getTypeConstant($type) {
        switch (strtolower($type)) {
            case "bigint" :
                $returnType = \Edify\Database\Server::DBTYPE_BIGINT;
                break;
            case "int" :
                $returnType = \Edify\Database\Server::DBTYPE_INT;
                break;
            case "tinyint" :
                $returnType = \Edify\Database\Server::DBTYPE_TINYINT;
                break;
            case "smallint" :
                $returnType = \Edify\Database\Server::DBTYPE_SMALLINT;
                break;
            case "char" :
                $returnType = \Edify\Database\Server::DBTYPE_CHAR;
                break;
            case "varchar" :
                $returnType = \Edify\Database\Server::DBTYPE_VARCHAR;
                break;
            case "datetime" :
                $returnType = \Edify\Database\Server::DBTYPE_DATETIME;
                break;
            case "text" :
                $returnType = \Edify\Database\Server::DBTYPE_TEXT;
                break;
            case "smalltext" :
                $returnType = \Edify\Database\Server::DBTYPE_SMALLTEXT;
                break;
            default:
                $returnType = null;
        }
        return $returnType;
    }

    function getLockStatement(){
        return $this->driver->getLockStatement();
    }
}

?>
