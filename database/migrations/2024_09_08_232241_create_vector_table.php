<?php
use Illuminate\Database\Migrations\Migration;
use MHz\MysqlVector\VectorTable;

class CreateVectorTable extends Migration
{
    protected $mysqli;

    public function up()
    {
        // Use config() to get the database credentials
        $host = config('database.connections.mysql.host');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $database = config('database.connections.mysql.database');

        // Initialize MySQLi connection
        $mysqli = new \mysqli($host, $username, $password, $database);

        // Check for connection errors
        if ($mysqli->connect_error) {
            throw new \Exception('MySQL connection failed: ' . $mysqli->connect_error);
        }

        // Define table name, dimension, and engine
        $tableName = 'my_vector_table';
        $dimension = 1536;
        $engine = 'InnoDB';

        // Initialize the VectorTable
        $vectorTable = new VectorTable($mysqli, $tableName, $dimension, $engine);

        // Call the initialize() method to create the table and the COSIM function
        try {
            $vectorTable->initialize();
        } catch (\Exception $e) {
            throw new \Exception('Failed to initialize vector table: ' . $e->getMessage());
        }

        // Close the MySQLi connection
        $mysqli->close();
    }

    public function down()
    {
        // Use config() to get the database credentials
        $host = config('database.connections.mysql.host');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $database = config('database.connections.mysql.database');

        // Initialize MySQLi connection
        $mysqli = new \mysqli($host, $username, $password, $database);

        // Drop the COSIM function and the table
        try {
            $mysqli->query("DROP FUNCTION IF EXISTS COSIM");
            $mysqli->query("DROP TABLE IF EXISTS my_vector_table");
        } catch (\Exception $e) {
            throw new \Exception('Failed to drop the table or function: ' . $e->getMessage());
        }

        // Close the MySQLi connection
        $mysqli->close();
    }
};
