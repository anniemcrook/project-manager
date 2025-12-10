<?php

/** 
 * File: project_delete.php
 * Allows logged-in users to delete their own projects.
 */

// Start the session to manage user authentication state
session_start();

// Redirect unauthenticated users to the login page
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

// Include the database connection file
include 'connectdb.php';

// Logged-in user's UID
$userid = $_SESSION['uid'];

// Get the project ID from URL
$pid = $_GET['pid'] ?? null;

if (!$pid) {
    // No project chosen – go back to My Projects
    header("Location: myprojects.php?error=noproject");
    exit();
}

try {
    // Delete only if the project belongs to this user
    $delete = $db->prepare("DELETE FROM projects WHERE pid = ? AND uid = ?");
    $delete->execute([$pid, $userid]);

    if ($delete->rowCount() > 0) {
        // Successfully deleted
        header("Location: myprojects.php?deleted=1");
        exit();
    } else {
        // Nothing deleted – wrong pid or not your project
        header("Location: myprojects.php?error=notfound");
        exit();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    header("Location: myprojects.php?error=db");
    exit();
}
