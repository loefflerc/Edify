<?php
/** A class to replace all of the CRUD functions for a specific database
 * 
 */

namespace Edify\Database;

/**
 * The class Record that allows you to request a list of objects from a table
 * or to pass a database table object and tell it to save it will then work out 
 * if it is an insert or update that is required to save it.
 *
 * @author IrishAdo <irishado@php-programmers.net>
 */
class Record {

    /**
     *
     * @var String the log handle to use to when reporting this class to the
     * error log
     */
    private $logHandle = "record";
    private $model = null;
    private $databaseName = null;
    private $tableName = NULL;
    private $primaryKey = NULL;
    private $keys = Array();
    private $insertKeys = Array();
    private $insertValues = Array();
    private $updateStatementList = Array();
    private $blankObject = null;
    private $dbObject = null;

    /**
     *
     * @param type $model
     */
    function __construct($model, $dbObject) {
        $this->dbObject = $dbObject;
        $this->model = $model;
        $this->blankObject = new $this->model();

        $this->databaseName = $this->blankObject->databaseName;
        $this->primaryKey = $this->blankObject->getPrimaryKey();
        $this->tableName = $this->blankObject->getTableName();
        $this->keys = $this->blankObject->getKeys();

        foreach ($this->keys as $field) {
            if ($this->primaryKey != $field) {
                $this->insertKeys[count($this->insertKeys)] = $field;
                $this->insertValues[count($this->insertValues)] = ":$field";
                $this->updateStatementList[count($this->updateStatementList)] = "$field=:$field";
            }
        }
    }

    /**
     * execute a SQL statement against the model
     * @author IrishAdo <irishado@php-programmers.net>
     * @param String SQL Statement to execute
     * @param Boolean $cache
     * @return \Edify\Controller\model
     */
    function execute($Statement, $parameters = Array(),$fetchType = \PDO::FETCH_CLASS) {
        //\Edify\Utils\Log::debugLog("[".$this->logHandle."]", print_R($parameters, true));
        $Statement->execute($parameters);
        if($fetchType == \PDO::FETCH_CLASS){
            return $Statement->fetchAll($fetchType, $this->model);
        } else {
            return $Statement->fetchAll($fetchType);
        }
    }

    /**
     * 
     * @param Array  List of paramaters 
     * @param Integer $limit
     * @param String $orderby
     * @param Boolean $cache
     * @return type
     */
    function select($parameters = Array(), $orderby = "") {
        $sql = "select ";
        $sql .= implode(", ", $this->keys);
        $sql .= " from " . $this->tableName . " " . $this->dbObject->getLockStatement() . " where ";
        foreach ($parameters as $key => $value) {
            if (in_array($key, $this->keys)) {
                $sql .= $key . " = :$key and ";
            }
        }
        $sql .= " 1=1 ";
        if ($orderby != "") {
            $sql .= " order by $orderby";
        }
        error_log($sql);
        $pdoStatement = $this->dbObject->prepare($sql);
        return $this->execute($pdoStatement, $parameters);
    }

    function saveList($list) {
        if (is_array($list)) {
            foreach ($list as $index => $record) {
                $list[$index] = $this->save($record);
            }
        }
        return $list;
    }

    function save($obj) {
        \Edify\Utils\Log::debugLog("[" . $this->logHandle . "]", "attempting to save object [" . $this->databaseName . "." . $obj->tableName . " => $this->model] ");
        if (is_object($obj)) {
            if ($obj->getPrimaryKeyValue() < 0) {
                return $this->insert($obj);
            } else {
                return $this->update($obj);
            }
        }
    }

    function delete($mixed) {
        if ($this->primaryKey == "" || is_null($this->primaryKey)) {
            return false;
        }
        if (is_object($mixed) && ("model_" . $this->tableName == $this->model)) {
            $uniqueId = $mixed->getPrimaryKeyValue();
        } else {
            $uniqueId = $mixed;
        }
        execute_query("delete from " . $this->tableName . " where $this->primaryKey = $uniqueId", $this->databaseName);
        return true;
    }

    private function insert($obj) {
        $sql = "insert into " . $this->tableName . " (" . implode(", ", $this->insertKeys) . ") values (" . implode(", ", $this->insertValues) . ");";
        if ($this->primaryKey == "" || is_null($this->primaryKey)) {
            
        } else {
            $sql .= $this->dbObject->getInsertIdStatement();
        }
        $pdoStatement  = $this->dbObject->prepare($sql);
        $parameters = $this->prepareParameters($obj,$this->insertKeys);
        $primaryKey = $this->primaryKey;

        $results = $this->execute($pdoStatement, $parameters, \PDO::FETCH_ASSOC);
        error_log($sql);
        error_log(print_R($results,true));
        exit();
        // recordset zero , record zero, field
        if (!is_null($results[0]["scope_identifier"])) {
            $obj->$primaryKey = $results[0]["scope_identifier"];
        } else {
            $obj->$primaryKey = $results[0]["SCOPE_ID"];
        }
        \Edify\Utils\Log::debugLog("[model]", "Setting primary key after insert [$primaryKey] = [" . $obj->$primaryKey . "]");

        return $obj;
    }

    private function update($obj) {
        $sql = "update " . $this->tableName . " set " . implode(",", $this->updateStatementList) . " where $this->primaryKey=:$this->primaryKey";

        if ($sql) {
         //   execute_query($sql, $this->databaseName);
        }
        return $obj;
    }

    /**
     * bind the object values to a parameter Array so that we can execute the statement
     * @param PDOStatement $statement
     * @param Array $obj
     * @param Array $keys
     * @param Boolean $bindNulls
     * @return mixed
     */
    private function prepareParameters($obj, $keys, $bindNulls = true) {
        $parameters = Array();
        if (is_object($obj)) {
            foreach ($keys as $field) {

                if (is_object($obj->$field) && get_class($obj->$field) . "" == "DateTime") {
                    $value = $obj->$field->format(\Edify\Database\Server::DATETIME_SAVEFORMAT);
                } else {
                    $value = $obj->$field;
                }
                $parameters[":" . $field] = $value;
            }
        }
        return $parameters;
    }

    /**
     * create a new object of this typ eof record.
     * @param type $data
     * @return \Edify\Database\model
     */
    function newRecord($data = Array()) {
        return new $this->model($data);
    }

}





?>
