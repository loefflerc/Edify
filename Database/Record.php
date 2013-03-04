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
     * constructor takes model name & database object to work on.
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
    function execute($Statement, $parameters = Array(), $fetchType = \PDO::FETCH_CLASS) {
        //\Edify\Utils\Log::debugLog("[".$this->logHandle."]", print_R($parameters, true));
        $Statement->execute($parameters);
        if ($fetchType == \PDO::FETCH_CLASS) {
            return $Statement->fetchAll($fetchType, $this->model);
        } else {
            return $Statement->fetchAll($fetchType);
        }
    }

    /**
     * select a recrod based on the fields supplied.
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
        $pdoStatement = $this->dbObject->prepare($sql);
        return $this->execute($pdoStatement, $parameters);
    }
    /**
     * save a list of objects for this record type.
     *
     * @param type $list
     * @return type
     */
    function saveList($list) {
        if (is_array($list)) {
            foreach ($list as $index => $record) {
                $list[$index] = $this->save($record);
            }
        }
        return $list;
    }

    /**
     * Save an object back to the database
     * @param Object $obj this is a database Model object that represents data in a record
     * @return Object return the supplied object insert make primary key have value.
     */
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

    /**
     * Delete a record of this type by its id
     * @param type $mixed
     * @return boolean
     */
    function delete($mixed) {
        if ($this->primaryKey == "" || is_null($this->primaryKey)) {
            return false;
        }
        if (is_object($mixed) && ("model_" . $this->tableName == $this->model)) {
            $uniqueId = $mixed->getPrimaryKeyValue();
        } else {
            $uniqueId = $mixed;
        }
        $pdoStatement = $this->dbObject->prepare("delete from " . $this->tableName . " where $this->primaryKey = :uniqueId");
        $pdoStatement->execute(Array(":uniqueId" => $uniqueId));
        return true;
    }
    /**
     * take an object and insert it into a table then extract the primary key
     * and update the record.  return the object with its new id.
     * @param type $obj
     * @return type
     */
    private function insert($obj) {
        $sql = "insert into " . $this->tableName . " (" . implode(", ", $this->insertKeys) . ") values (" . implode(", ", $this->insertValues) . ");";

        $pdoStatement = $this->dbObject->prepare($sql);
        $parameters = $this->prepareParameters($obj, $this->insertKeys);
        $primaryKey = $this->primaryKey;
        $results = $this->execute($pdoStatement, $parameters, \PDO::FETCH_ASSOC);
        if (!($this->primaryKey == "" || is_null($this->primaryKey))) {
            $obj->$primaryKey = $this->dbObject->getInsertId();
        }
        \Edify\Utils\Log::debugLog("[model]", "Setting primary key after insert [$primaryKey] = [" . $obj->$primaryKey . "]");

        return $obj;
    }
    /**
     * update an existing record
     * @param type $obj
     * @return type
     */
    private function update($obj) {
        $sql = "update " . $this->tableName . " set " . implode(",", $this->updateStatementList) . " where $this->primaryKey=:$this->primaryKey";
        $pdoStatement = $this->dbObject->prepare($sql);
        $parameters = $this->prepareParameters($obj, $this->keys);
        $this->execute($pdoStatement,$parameters, \PDO::FETCH_ASSOC);
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
