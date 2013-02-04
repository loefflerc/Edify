<?php

namespace Edify\Database\Drivers;
/**
 *
 *
 * @licence http://php-programmers/licences/Freetard FreeTard licence
 * @author IrishAdo <irishado@php-programmers.net>
 */
class Mssql {
    private $parentObject = null;
    private $connection = null;
    CONST DRIVER_TYPE = "mssql";

    function __construct($parent){
        $parentObject = $parent;
    }

    /**
     * Using this driver connect to a specified server and create a connection
     * 
     * @param type $database
     * @param type $host
     * @param type $username
     * @param type $password
     * @return \PDO
     */
    function getConnection($database, $host, $username, $password){
        $this->connection = new \PDO($this->getType().":dbname=".$database.";host=".$host, $username,$password );
        return $this->connection;
    }

    /**
     *
     * @return string
     */
    function getSchema(){
        $statement =  $this->connection->prepare("SELECT
                OBJECT_SCHEMA_NAME(T.[object_id],DB_ID()) AS [Schema],   
                T.[name] AS [table_name], AC.[name] AS [column_name],   
                TY.[name] AS system_data_type, AC.[max_length],  
                AC.[precision], AC.[scale], AC.[is_nullable], AC.[is_ansi_padded], AC.[is_identity]
            FROM sys.[tables] AS T   
                INNER JOIN sys.[all_columns] AC ON T.[object_id] = AC.[object_id]  
                INNER JOIN sys.[types] TY ON AC.[system_type_id] = TY.[system_type_id] AND AC.[user_type_id] = TY.[user_type_id]   
            WHERE T.[is_ms_shipped] = 0  
            ORDER BY T.[name], AC.[column_id]");

        $statement->execute();

        return $statement->fetchAll();
    }
    /**
     *
     * @return String the type of the database driver
     */
    function getType(){
        return self::DRIVER_TYPE;
    }
}

?>
