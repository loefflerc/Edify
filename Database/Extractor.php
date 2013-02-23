<?php

namespace Edify\Database;

/**
 * Extract a Database and put each table into a Model Object
 *
 * @licence http://php-programmers/licences/Freetard FreeTard licence
 * @author IrishAdo <irishado@php-programmers.net>
 */
class Extractor {

    private $tables = Array();
    private $pdoObject = null;
    private $destinationPath = null;
    private $destinationNameSpace = null;

    /**
     * Create a new instance of the Extractor handing it the DB connection to
     * work with, the destiniation path and the Namespace that the generated
     * Classes should be defined under.
     *
     * @param PDO $pdoObject
     * @param String $destinationPath path to the save the extracted models.
     * @param String $destinationNameSpace what should the namespace be for this
     * class.
     */
    function __construct($pdoObject, $destinationPath, $destinationNameSpace) {
        $this->pdoObject = $pdoObject;
        $this->destinationPath = $destinationPath;
        $this->destinationNameSpace = $destinationNameSpace;
    }

    /**
     * Run the extractor against the supplied database connection
     */
    function run(){
        $tableData = $this->pdoObject->getSchema();

        foreach ($tableData as $table => $tableDefinition) {
            $tableData[$table]["primary"] = $this->findPrimary($tableDefinition["columns"]);
            $tableData[$table]["fixedTableName"] = $this->fixTableName($table);
        }
        $this->writeFiles($tableData);
    }

    /**
     * Fix the table name to be a safe name for a class
     *
     * @param String $name
     * @return string
     */
    function fixTableName($name) {
        if ((strtoupper($name) == $name) || (strtolower($name) == $name)) {
            $name = substr(strtoupper($name), 0, 1) . strtolower(substr($name, 1));
        }
        if (strpos($name, "_") !== false) {
            $parts = explode("_", $name);
            $name = "";
            foreach ($parts as $word) {
                $word = substr($word, 0, 1) . substr($word, 1);
                $name .= $word;
            }
        }
        return $name;
    }

    /**
     * Loop through the list of column names and check if any were a primary key
     *
     * @param Array $columns
     * @return string
     */
    function findPrimary($columns) {
        foreach ($columns as $column) {
            if ($column["is_identity"] == 1) {
                return $column["column_name"];
            }
        }
        return "";
    }

    /**
     * write the table out as a php class.
     *
     * @param Array $tables
     */
    function writeFiles($tables) {
        foreach ($tables as $table => $properties) {
            $class = $properties["fixedTableName"];
            $buffer = "<?php
namespace " . $this->destinationNameSpace . $properties["schema_name"] . ";
/**
  * $class implements a Database Model Object
  *
  * @licence http://php-programmers/licences/Freetard FreeTard licence
  * @author IrishAdo <irishado@php-programmers.net>
  */;
class $class extends \Edify\Database\Model {
    var \$databaseName = \"" . $properties["schema_name"] . "\";
    var \$tableName    = \"" . $table . "\";
    var \$primaryKey   = \"" . $properties["primary"] . "\";
    var \$properties   = Array(\n\t\t";
            $max = count($properties["columns"]) - 1;
            foreach ($properties["columns"] as $index => $columnData) {
                $buffer .= "\"" . $columnData["column_name"] . "\"=>\"\"";
                if ($max != $index) {
                    $buffer .=", \n\t\t";
                }
            }
            $buffer .="\n\t);
}
?>";
            print $buffer;

            $filename = $this->destinationPath . "/" . $properties["schema_name"] . "/" . $class . ".php";

            \Edify\Utils\Log::debugLog("[Edify\Database\Extractor]", "Saving class $class to $filename");
            if (!mkdir(dirname($filename), 0770, true)) {
                \Edify\Utils\Log::debugLog("[Edify\Database\Extractor]", "Failed to create directory " . dirname($filename));
            }

            $filePointer = fopen($filename, "w");
            fwrite($filePointer, $buffer);
            fclose($filePointer);
            \Edify\Utils\Log::debugLog("[Edify\Database\Extractor]", "wrote $filename");
        }
    }

}

?>