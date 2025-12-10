    <?php
    /**
     * File: logout.php
     * Handles logout function, destroys all session data.
     * Redirects to login page.
     */

    // Start the session to manage user authentication state
    session_start();

    $_SESSION = array();

    // Destroy all session data to log out the user
    session_destroy();

    // Redirect to the login page after logout
    header("Location: login.php"); 
    exit(); // Ensure no further code is executed
    ?>

