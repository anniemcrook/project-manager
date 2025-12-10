<?php
/**
 * File: header.php
 * Initializes and secures user sessions.
 * Applies automatic inactivity logout.
 * Generates CSRF tokens for protected forms.
 * Loads the global HTML <head> section and navbar.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Session inactivity timeout (15 minutes)
 * Applies to all authenticated pages except login.php and register.php
 */
$current_page = basename($_SERVER['PHP_SELF']);

if (!in_array($current_page, ['login.php', 'register.php'])) {
    $inactive_limit = 900; // 15 minutes
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_limit)) {
        session_unset();
        session_destroy();
        header("Location: login.php?expired=true");
        exit();
    }
    $_SESSION['last_activity'] = time();
}

/**
 * CSRF token generation
 * Creates a unique token per session for form security
 */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- The main HTML setup for the entire document -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Manager</title> <!-- Page title stand-in for company name -->

    <!-- Main CSS -->
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Montserrat:wght@100..900&display=swap"
        rel="stylesheet">
</head>
<body>
<!-- Include the navbar on every page -->
<?php include('includes/navbar.php'); ?>

