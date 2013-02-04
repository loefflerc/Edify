<?php

namespace Edify\Database\Drivers;

/**
 *
 *
 * @licence http://php-programmers/licences/Freetard FreeTard licence
 * @author IrishAdo <irishado@php-programmers.net>
 */
class Mysql {

    private $parentObject = null;
    private $connection = null;
    private $database = null;

    CONST DRIVER_TYPE = "mysql";

    function __construct($parent) {
        $parentObject = $parent;

        // check if the driver we are using has been configured in the php
        //configuration
        $parentObject->checkForDriver($this->getType());
    }

    /**
     * Using this driver connect to a specified server and create a connection
     * this driver will attach to a MySQL server using PDO.
     * 
     * @param type $database
     * @param type $host
     * @param type $username
     * @param type $password
     * @return \PDO
     */
    function getConnection($database, $host, $username, $password) {
        $this->database = $database;
        $this->connection = new \PDO($this->getType() . ":dbname=" . $database . ";host=" . $host, $username, $password);
        return $this->connection;
    }

    /**
     * Get the schema from the current connection which should limit us to the
     * database defined in the PDO connection.
     *
     * @return Array the Schema definition.
     */
    function getSchema() {
        $results = array();
        $statement = $this->connection->prepare("show tables");
        $statement->execute();

        $tableList = $statement->fetchAll(\PDO::FETCH_NUM);

        foreach ($tableList as $tableRow) {
            $columns = $this->connection->query("show columns in " . $tableRow[0]);
            foreach ($columns as $row) {
                list($colType, $colPrecision, $colExtra) = $this->parentObject->examineColumnType($row["Type"]);
                if(!isset($results[$tableRow[0]])){
                    $results[$tableRow[0]] = Array(
                        "schema_name" => $this->database,
                        "columns" => Array()
                    );
                }
                $results[$tableRow[0]]["columns"][count($results[$tableRow[0]]["columns"])] = Array(
                    "column_name" => $row["Field"],
                    "system_data_type" => $colType,
                    "max_length" => "",
                    "precision" => $colPrecision,
                    "scale" => "",
                    "extra" => $colExtra,
                    "is_nullable" => ($row["Null"] == "YES" ? 1 : 0),
                    "is_ansi_padded" => "",
                    "is_identity" => ($row["Key"] == "PRI" ? 1 : 0),
                    "is_autoincrement" => (strpos($row["Extra"], "auto_increment") === true ? 1 : 0)
                );
            }
        }

        return $results;
    }

    /**
     * what is the type for the database connection
     *
     * @return String the type of the database driver
     */
    function getType() {
        return self::DRIVER_TYPE;
    }

    
}

?>
