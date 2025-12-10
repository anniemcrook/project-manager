<?php

/** 
 * File: login.php
 * Handles user login functionality for the project management system.
 * This script verifies login credentials, implements basic account lockout
 * protection, upgrades outdated password hashes, and starts a secure session.
 */

// Start the session to manage user authentication state
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include the database connection file
include 'connectdb.php';

// Redirect logged-in users directly to the homepage
if (isset($_SESSION['uid'])) {
    header("Location: index.php");
    exit();
}

// Process login form submission
if (isset($_POST['submit'])) {

    // CSRF protection
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    // Retrieve and sanitize user input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user record from the database based on username
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Check for temporary account lockout (5 failed attempts within 5 minutes)
    if ($user && $user['failed_attempts'] >= 5) {
        $lastAttempt = strtotime($user['last_attempt']);
        $lockDuration = 5 * 60; // Lockout period: 5 minutes

        if (time() - $lastAttempt < $lockDuration) {
            // Deny login during lockout
            $error = "Account locked due to too many failed logins. Try again later.";
        } else {
            // Reset failed attempts after lockout expires
            $db->prepare("UPDATE users SET failed_attempts = 0 WHERE username = ?")
                ->execute([$username]);
        }
    }

    // If not locked, proceed with password verification
    if (!isset($error)) {
        if ($user && password_verify($password, $user['password'])) {

            // Automatically rehash old password hashes if PHP's default algorithm has changed
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
                $update->execute([$newHash, $username]);
            }

            // Reset failed login attempts upon successful authentication
            $db->prepare("UPDATE users SET failed_attempts = 0 WHERE username = ?")
                ->execute([$username]);

            // Store username in session and redirect to profile page
            $_SESSION['username'] = $username;
            $_SESSION['uid'] = $user['uid'];
            $_SESSION['firstname'] = $user['firstname'];

            // Redirect to home page after successful login
            header("Location: index.php");
            exit(); // Stop execution after redirect

        } else {
            // Handle incorrect login attempt
            if ($user) {
                // Increment failed login attempts and update timestamp
                $db->prepare("UPDATE users 
                                SET failed_attempts = failed_attempts + 1, 
                                last_attempt = NOW() 
                                WHERE username = ?")
                    ->execute([$username]);
            }
            // Generic error message (prevents attackers from learning whether the username exists)
            $error = "Invalid username or password.";
        }
    }
}

// Include the header file (layout, navigation, bootstrap, etc.)
include 'includes/header.php';
?>

<div class="page-content">

    <?php
    // Display session time-out message if session expired
    if (isset($_GET['expired']) && $_GET['expired'] == 'true') {
        echo "<p style='color: red;'>Your session has expired. Please log in again.</p>";
    }
    ?>
    <!-- Main page content -->
    <div class="form-container">
        <h2>User Login</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <!-- User login form -->
        <form action="login.php" method="post">
            <!-- CSRF token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="submit" value="1">
            <!-- Username -->
            <div class="form-group mb-3">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" class="form-control"
                    value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                    required>
            </div>
            <!-- Password -->
            <div class="form-group mb-4">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <!-- Submit button -->
            <input type="submit" value="Login" id="submit-btn" class="btn btn-primary btn-block">
            <br>
            <!-- Link to register -->
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>
</div>

<?php
// Include the footer file
include 'includes/footer.php';
?>