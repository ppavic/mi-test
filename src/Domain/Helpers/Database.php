<?php

namespace App\Domain\Helpers;

use PDO;
use PDOException;

/**
 * Database class handles connection and basic operations.
 */
class Database
{

    private $pdo;

    /** connection parameters */
    private array $param = [
        'user' => 'root',
        'pass' => '123',
        'db_name' => 'mitest',
        'host' => 'localhost',
    ];

    public function __construct()
    {
        // assuming that mitest database exists - > connecting to database using connection parameters
        try {

            $this->pdo = new PDO('mysql:host=' . $this->param["host"] . ";dbname=" . $this->param["db_name"], $this->param['user'], $this->param['pass']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (\Exception $e) {
            echo "Unable to connect to database: " . $e->getMessage();
        }
    }

    /**
     * Function that creates table in database.
     *
     * @param string $tableName Name of table that will be created
     * @param array $columns Name and Type of columns to create ['id' => 'int PRIMARY KEY AUTO_INCREMENT', 'Name' => 'varchar(255)', ...]
     * @return void
     */
    public function createTable(string $tableName, array $columns)
    {
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (";

        foreach ($columns as $columnName => $columnType) {

            $sql .= "$columnName $columnType, ";
        }

        $sql = rtrim($sql, ', ') . ')';

        try {
            $this->pdo->exec($sql);

        } catch (PDOException $e) {
            echo "Unable to create table: " . $e->getMessage();
        }
    }
    /**
     * Function that drops table
     *
     * @param string $tableName Name of table that will be dorped.
     * @return void
     */
    public function dropTable(string $tableName): void
    {
        try {

            $sql = "DROP TABLE IF EXISTS $tableName";
            $this->pdo->exec($sql);

        } catch (\Exception $e) {

            echo 'Failed to drop table: ' . $e->getMessage();

        }
    }

    /**
     * Function that inserts row of given data
     *
     * @param string $tableName Name of table that will be filled
     * @param array $data Data that will be filled to table ['<attribute name> => <value>, ...]
     * @param boolean $rollback controls if rollback should be performed
     * @return void
     */
    public function insertRow(string $tableName, array $data, bool $rollback = false): void
    {

        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));

        // prepared sql statement to prevenet sql injection
        $sql = "INSERT INTO $tableName ($columns) VALUES ($values)";
        $stmt = $this->pdo->prepare($sql);

        //tranaction
        $this->pdo->beginTransaction();

        try {

            $stmt->execute(array_values($data));

        } catch (PDOException $e) {
            // rollback because of error
            $this->pdo->rollBack();
            echo "Error executing insertRow." .  $e->getMessage();
        }

        // user rollback
        if ($rollback) {
            $this->pdo->rollBack();
        }else{
            $this->pdo->commit();
        }

    }

    /**
     * Fuction that drops row based o condition
     *
     * @param string $table Table name from which row will be droped
     * @param array $criteria Criteria list ['id' => 3, 'Name' => 'Jhon' ...]
     * @param boolean $rollback controll if rollback should be performed
     * @return void
     */
    public function dropRow(string $table, array $criteria, $rollback = false)
    {
        $conditions = [];

        /**
         * Arange criteria on key, and set value to ?
         */
        foreach ($criteria as $column => $value) {
        
            $conditions[] = "$column = ?";
        }

        /** for multiple conditions */
        $where = implode(' AND ', $conditions);

        $sql = "DELETE FROM $table WHERE $where";
        
        $stmt = $this->pdo->prepare($sql);
        
        $this->pdo->beginTransaction();
        try {

            $stmt->execute(array_values($criteria));
        
        } catch (PDOException $e) {
        
            //error rollback
            $this->pdo->rollBack();
        }

        // user rollback
        if ($rollback) {
            $this->pdo->rollBack();
        }else{
            $this->pdo->commit();
        }

    }

}
