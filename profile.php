<?php

/**
 * File: profile.php
 * Displays the logged-in user's profile information retrieved from the database.
 * The page ensures that only authenticated users can access it, fetches their
 * data securely using prepared statements, and handles database errors gracefully.
 * Provides an option to change the password.
 */

// Start or resume the session
session_start();

// Enforce authentication — redirect to login if user is not logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

// Include database connection file
include 'connectdb.php';

// Fetch user profile information from the database
try {
    // Retrieve full user details based on the user ID stored in the session
    $stmt = $db->prepare("SELECT * FROM users WHERE uid = ?");
    $stmt->execute([$_SESSION['uid']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $db->prepare("SELECT COUNT(*) AS project_count FROM projects WHERE uid = ?");
    $stmt->execute([$_SESSION['uid']]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error for debugging (not exposed to user)
    error_log($e->getMessage());
    // Display a generic, user-friendly message
    echo "Sorry, we're having technical issues. Please try again later.";
    $user = null;
}

// Include the header file (layout, navigation, bootstrap, etc.)
include 'includes/header.php';
?>

<!-- Main page content -->
<div class="page-content">
    <h2>User Profile</h2>

    <!-- Display user profile details if retrieved successfully -->
    <div class="card bg-light mb-3" id="profile-card" style="max-width: fit-content">
        <div class="card-header">My Personal Details:</div>
        <?php if ($user): ?>
            <div class="card-body">
                <p class="card-text">
                    <!-- Display user details -->
                    <strong>Full Name:</strong> <?php echo htmlspecialchars($user['firstname'] . " " . $user['lastname']); ?></br>
                    <strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></br>
                    <strong>Registered On:</strong>
                    <?php echo htmlspecialchars($user['created_at'] ?? 'Unknown'); ?></br>
                    <strong>Registered Projects:</strong> <?php echo $count['project_count']; ?>
                </p>
            </div>
        <?php else: ?>
            <!-- Fallback in case user data could not be fetched -->
            <p>Unable to load profile information.</p>
        <?php endif; ?>
    </div>
    <!-- Change password button -->
    <div class="text-center my-4">
        <a href="change_password.php" class="btn btn-primary">Change password</a>
    </div>
</div>

<?php
// Include the footer file
include 'includes/footer.php';
?>