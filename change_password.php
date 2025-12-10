<?php

/**
 * File: change_password.php
 * Allows logged-in users to update password, upholds security with password validation.
 */

// Start the session to manage user authentication state
session_start();

// Block unauthenticated users
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

// Include database connection file
include 'connectdb.php';

$userid = $_SESSION['uid'];
$errors = [];
$success = "";

// Fetch current user info
try {
    $stmt = $db->prepare("SELECT * FROM users WHERE uid = ?");
    $stmt->execute([$userid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Sorry, we're having technical issues.");
}

// Handle form submission
if (isset($_POST['submit'])) {

    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    // Get form values
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_new_password']);

    // Password regex for 8+ chars, upper, lower, number, special
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    // Check if user is attempting a password change
    $is_attempt = ($current_password !== "" || $new_password !== "" || $confirm_password !== "");

    if ($is_attempt) {

        // Require all fields
        if ($current_password === "" || $new_password === "" || $confirm_password === "") {
            $errors[] = "All password fields must be completed.";
        }
        // Verify old password
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        }
        // Match new + confirmation
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        }
        // Strong password check
        if (!preg_match($pattern, $new_password)) {
            $errors[] = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
        }
        // If valid → update password
        if (empty($errors)) {
            try {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE users 
                    SET password = ?
                    WHERE uid = ?
                ");
                $stmt->execute([$hashed, $userid]);
                $success = "Your password has been updated successfully!";
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    } else {
        $errors[] = "Please fill in all fields to change your password.";
    }
}

include 'includes/header.php';
?>

<!-- Main page content -->
<div class="page-content">
    <h2>Change Password</h2>

    <div class="form-container">
        <!-- Error message -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
            </div>
        <?php endif; ?>
        <!-- Success message -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" id="change-password-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="submit" value="1">
            <!-- Current Password -->
            <div class="form-group mb-3">
                <label for="current_password">Your current password:</label>
                <input type="password" id="current_password" name="current_password" class="form-control" required>
                <div class="invalid-feedback"></div>
            </div>
            <!-- New Password -->
            <div class="form-group mb-3">
                <label for="new_password">New password:</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required>
                <small>(At least 8 characters, including uppercase, lowercase, number, and special character)</small>
                <div class="invalid-feedback"></div>
            </div>
            <!-- Confirm Password -->
            <div class="form-group mb-4">
                <label for="confirm_new_password">Confirm new password:</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" required>
                <div class="invalid-feedback"></div>
            </div>
            <!-- Submit button -->
            <input type="submit" value="Update Password" class="btn btn-primary btn-block">
            <!-- Return to profile without making changes -->
            <p class="mt-3 text-center">
                <a href="profile.php">Return to My Profile</a>
            </p>
        </form>
    </div>
</div>

<?php
// Include the footer file
include 'includes/footer.php';
?>