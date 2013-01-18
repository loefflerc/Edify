<?php

/**
 * @package     Edify\Database
 */
namespace Edify\Database;

/**
 * @uses        Exception
 * @uses        UnexpectedValueException
 * @uses        PDO
 * @uses        \Edify\Database\Connection
 */
use \Exception;
use \UnexpectedValueException
use \PDO;
use \Edify\Database\Connection;

/**
 * Database facotry class
 */
class Database
{
    /**
     * Establish connection to a database driver
     *
     * @param   array   $config
     *
     * @throws  \Exception
     * @throws  \UnexpectedValueException
     * @throws  \PDO
     * @return  \Edify\Database\Connection
     */
    public static function connect(array $config)
    {
        // Validate the configurations 
        if (!array_key_exists('hostname', $config)) {
            throw new Exception('Missing hostname');
        }

        if (!array_key_exists('username', $config)) {
            throw new Exception('Missing username');
        }

        if (!array_key_exists('password', $config)) {
            throw new Exception('Missing password');
        }

        if (!array_key_exists('database', $config)) {
            throw new Exception('No database has been selected');
        }

        if (!array_key_exists('driver', $config)) {
            throw new Exception('Missing driver');
        }

        // Get real driver path
        if (!($driverPath = realpath(dirname(__FILE__) . "/Drivers/{$config['driver']}.php"))) {
            throw new Exception('Unsupported driver')
        }

        // Extract driver name from the path
        $driverName = basename($driverPath, '.php');

        // Create driver namespace
        $driver = __NAMESPACE__ . "\Drivers\{$driverName}";

        // Instantiate the driver
        $driver = $driver::connect($config);

        // Make sure the driver has returned a valid PDO object
        if (!($driver instanceof PDO)) {
            $type = gettype($driver);
            throw new UnexpectedValueException("Expected driver to be instance of PDO. '{$type}' given.");
        }

        reutrn new Connection($driver, $config);
    }
}