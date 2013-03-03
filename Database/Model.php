<?php

namespace Edify\Database;

/**
 * A Database Model Object
 *
 * @licence http://php-programmers/licences/Freetard FreeTard licence
 * @author IrishAdo <irishado@php-programmers.net>
 */
class Model {

    CONST __UNDEFINED__ = "__UNDEFINED__";

	public $databaseName = "";
	public $tableName = "";
	public $primaryKey = "";
	public $properties = Array();

	public function __construct($record = null) {
		if ($record != null) {
			$this->assign($record);
		}
	}

	public function __get($key) {
		if (!isset($this->properties[$key])) {
			\Edify\Utils\Log::debugLog("[Edify\Database\Model]", "Attempting to retrieve a non existant key $key on $this->tableName");
		} else {
			return (isset($this->properties[$key]) && $this->properties[$key] !== self::__UNDEFINED__) ? $this->properties[$key] : null;
		}
		return null;
	}

	public function __set($key, $value) {
		if (!isset($this->properties[$key])) {
            \Edify\Utils\Log::debugLog("[Edify\Database\Model]", "Attempting to set a non existant key [$key] with the value [" . print_R($value,true) . "] on $this->tableName");
		} else {
			if (!is_null($value)) {
				$this->properties[$key] = $value;
			} else {
				$this->properties[$key] = self::__UNDEFINED__;
			}
		}
	}

	/** Assign an array of values into the object
	 * @param Array Associate key is the property to assign the value to.
	 */
	public function assign($record) {
		foreach ($record as $field => $value) {
			if (isset($this->properties[$field])) {
				if (!is_null($value)) {
					$this->properties[$field] = $value;
				} else {
					$this->properties[$field] = self::__UNDEFINED__;
				}
			}
		}
	}

	public function dump() {
		
		$buffer = "<div class='dumpRecord'>\n\t<h2>Type : ".$this->tableName."</h2>\n\t";
		$buffer .= "<ul>";
		foreach ($this->properties as $field => $value) {
			$buffer .= "\n\t\t<li>$field => [" . ($value == self::__UNDEFINED__ ? 'NULL' : $value) . "],</li>";
		}
		$buffer .= "\n\t</ul>\n</div>";
		echo $buffer;
	}
	public function getDump() {
		$buffer = "Type : ".$this->tableName;
		foreach ($this->properties as $field => $value) {
			$buffer .= "$field => [" . ($value == self::__UNDEFINED__ ? 'NULL' : $value) . "],\n";
		}
		return $buffer;
	}

	public function getKeys() {
		return array_keys($this->properties);
	}

	public function getTableName() {
		return $this->tableName;
	}

	public function getPrimaryKeyValue() {
		// if no primary key then always do an insert.
		if ($this->primaryKey == "" || is_null($this->primaryKey) || ($this->properties[$this->primaryKey] == self::__UNDEFINED__)) {
			return -1; // represents a insert
		}
		return $this->properties[$this->primaryKey];
	}

	public function getPrimaryKey() {
		return $this->primaryKey;
	}

}

?>
