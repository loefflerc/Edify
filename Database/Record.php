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
	function __construct($model,$dbObject) {
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
				$this->insertValues[count($this->insertValues)] = "[$field]";
				$this->updateStatementList[count($this->updateStatementList)] = "$field=[$field]";
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
	function execute($Statement, $parameters = Array()) {
		//\Edify\Utils\Log::debugLog("[".$this->logHandle."]", print_R($parameters, true));
        $Statement->execute($parameters);
        return $Statement->fetchAll(\PDO::FETCH_CLASS,$this->model);
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
		$sql .= implode( ", ", $this->keys );
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
        $pdoStatement = $this->dbObject->prepare( $sql );
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
		\Edify\Utils\Log::debugLog("[".$this->logHandle."]", "attempting to save object [" .$this->databaseName . "." . $obj->tableName . " => $this->model] ");
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
		if ($this->primaryKey == "" || is_null($this->primaryKey)) {
			$sql = $this->formatSQL("insert into " . $this->tableName . " (" . implode(", ", $this->insertKeys) . ") values (" . implode(", ", $this->insertValues) . ");", $obj);
			if($sql){
				execute_query($sql, $this->databaseName);
			}
		} else {
			$sql = $this->formatSQL("insert into " . $this->tableName . " (" . implode(", ", $this->insertKeys) . ") values (" . implode(", ", $this->insertValues) . ");\nSELECT scope_identity() as SCOPE_IDENTITY_ID, @@IDENTITY as SCOPE_ID;", $obj);
			try{
				$results = execute_query($sql, $this->databaseName);
			} catch(\Database_Exception $exception){
				error_log("<li>Error executing the following SQL -> ".$sql."</li>");
				return null;
			}
			$primaryKey = $this->primaryKey;
			// recordset zero , record zero, field 
			if(!is_null($results[0][0]["SCOPE_IDENTITY_ID"])){
				$obj->$primaryKey = $results[0][0]["SCOPE_IDENTITY_ID"];
			} else {
				$obj->$primaryKey = $results[0][0]["SCOPE_ID"];
			}
			\Edify\Utils\Log::debugLog("[model]", "Setting primary key after insert [$primaryKey] = [".$obj->$primaryKey."]");
			
		}
		return $obj;
	}

	private function update($obj) {
		$sql = $this->formatSQL("update " . $this->tableName . " set " . implode(",", $this->updateStatementList) . " where $this->primaryKey=[$this->primaryKey]", $obj);
		
		if($sql){
			execute_query($sql, $this->databaseName);
		}
		return $obj;
	}

	private function formatSQL($sql, $obj) {
		
		if( is_object($obj) ){
			foreach ($this->keys as $field) {
				
				if( is_object($obj->$field) && get_class($obj->$field)."" == "DateTime"){
					$sql = str_replace("[$field]", !is_null($obj->$field) ? "'" . str_replace("'","''",$obj->$field->format(DATETIME_DATABASE_SAVE_FORMAT)) . "'" : "NULL", $sql);
				} else {
					$sql = str_replace("[$field]", !is_null($obj->$field) ? "'" . str_replace("'","''",$obj->$field) . "'" : "NULL", $sql);
				}
			}
			\Edify\Utils\Log::debugLog("[".$this->logHandle."]", $sql);
		
			return $sql;
		}else{
			return false;
		}
	}
	
	function newRecord($data=Array()){
		return new $this->model($data);
	}
}

?>
