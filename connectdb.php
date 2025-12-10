<?php

/**
 * File: connectdb.php
 * Establishes a secure connection to the MySQL database using PDO.
 * Included in all pages that require database access.
 */

// Database connection parameters
$db_servername = "localhost";        // Hostname of the database server
$db_username   = "root";             // MySQL username (default 'root' for local setups)
$db_password   = "";                 // MySQL password (default empty for local setups)
$db_name       = "projectwebsite";   // Database name

try {
    // Create PDO connection
    $db = new PDO(
        "mysql:host=$db_servername;dbname=$db_name;charset=utf8mb4",
        $db_username,
        $db_password
    );

    // Throw exceptions for debugging (recommended)
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ensure real prepared statements are used
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Generic error message (prevents leaking sensitive info)
    echo "Database connection failed.";
    error_log("PDO ERROR: " . $e->getMessage());
    exit();
}
