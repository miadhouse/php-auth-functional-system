<?php
/**
 * Database Configuration
 * PHP 8.4 Pure Functional Script
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'farsi');

/**
 * Connect to database and return connection
 *
 * @return PDO Database connection
 */
function get_db_connection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            die('Database connection failed. Please try again later.');
        }
    }
    
    return $pdo;
}

/**
 * Execute a query and return the result
 *
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters for prepared statement
 * @return array|false Query results or false on failure
 */
function db_query($sql, $params = []) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database Query Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Execute a query and return a single row
 *
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters for prepared statement
 * @return array|false Single row or false on failure
 */
function db_query_row($sql, $params = []) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Database Query Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Execute an insert, update, or delete query
 *
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters for prepared statement
 * @return int|false Number of affected rows or false on failure
 */
function db_execute($sql, $params = []) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Database Execute Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get the last inserted ID
 *
 * @return string|false The last inserted ID or false on failure
 */
function db_last_insert_id() {
    try {
        $pdo = get_db_connection();
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Database Last ID Error: ' . $e->getMessage());
        return false;
    }
}