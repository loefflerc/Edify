<?php

/**
 * @package     Edify\Database\Drivers
 */
namespace Edify\Database\Drivers;

/**
 * @uses        \PDO
 */
use \PDO;

/**
 * MySQL driver class
 */
class MySQL implements DriverInterface
{
    /**
     * Instantiate a PDO connection
     *
     * @param   array    $config
     *
     * @return  PDO
     */
    public static function connect(array $config)
    {
        // Create dsn string with the required values
        $dsn = "mysql:dbname={$config['database']};host={$config['hostname']}";

        // Add port to the dsn if set
        if (array_key_exists('port', $config)) {
            $dsn .= ";port={$config['port']}";
        }

        // Create a new PDO instance
        $pdo = new PDO($dsn, $config['username'], $config['password']);

        // Set database charset if set in the config array
        if (array_key_exists('charset', $config)) {
            $pdo->query("SET NAMES '{$config['charset']}'");
        }

        return $pdo;
    }
}