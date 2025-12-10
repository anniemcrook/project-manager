<?php

/**
 * File: register.php
 * Handles new user registration for the project management website.
 * This script validates form input, checks for duplicate usernames,
 * enforces password strength rules, and securely stores user credentials.
 */

// Start the session to manage user authentication state
session_start();

// Ensure a csrf token exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include the database connection file
include 'connectdb.php';

$errors = [];
$success = "";

// Form Validation Section
if (isset($_POST['submit'])) {

    // CSRF protection
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }


    // Retrieve and sanitize user input
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Input validation section
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (empty($firstname) || empty($lastname) || empty($email) || empty($username) || empty($password)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check valid email input
        $errors[] = "Please enter a valid email address.";
    } elseif (strlen($username) > 50) {
        // Restrain username character length
        $errors[] = "Username must be under 50 characters.";
    } elseif (strlen($firstname) > 50 || strlen($lastname) > 50) {
        // Restrain name character length
        $errors[] = "Names must be under 50 characters.";
    } elseif (strlen($email) > 255) {
        // Restrain email character length
        $errors[] = "Email must be under 255 characters.";
    } elseif (!preg_match($pattern, $password)) {
        // One single regex replaces all password checks
        $errors[] = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    } elseif ($password !== $confirm_password) {
        // Check if passwords match
        $errors[] = "Passwords do not match.";
    } else {
        // Database logic section
        try {
            // Check if username or email already exist (must be unique)
            $checkStmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $checkStmt->execute([$username, $email]);

            if ($checkStmt->rowCount() > 0) {
                // Duplicate username or email found
                $errors[] = "Username or email already exists.";
            } else {
                // Hash the password before storing
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user record securely using prepared statements
                $insertStmt = $db->prepare("INSERT INTO users (firstname, lastname, username, email, password) VALUES (?, ?, ?, ?, ?)");
                $insertStmt->execute([$firstname, $lastname, $username, $email, $hashed_password]);

                // Registration successful — show confirmation message
                $success = "Registration successful! You may now log in.";
            }
        } catch (PDOException $e) {
            // Handle any database errors 
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Include the header file (layout, navigation, bootstrap, etc.)
include 'includes/header.php';

?>

<div class="page-content">
    <!-- Registration form -->
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

        <form method="post" action="" id="registration-form" novalidate>
            <h2>User Registration</h2>

            <!-- CSRF token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="submit" value="1">
            <!-- Name -->
            <div class="form-row">
                <!-- first name -->
                <div class="col-md-6 mb-3">
                    <label for="firstname">First name:</label>
                    <input type="text" id="firstname" name="firstname" class="form-control" value="<?php echo isset($firstname) ? htmlspecialchars($firstname) : ''; ?>" required>
                    <div class="invalid-feedback"></div>
                </div>
                <!-- Last name -->
                <div class="col-md-6 mb-3">
                    <label for="lastname">Last name:</label>
                    <input type="text" id="lastname" name="lastname" class="form-control" value="<?php echo isset($lastname) ? htmlspecialchars($lastname) : ''; ?>" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <!-- Email -->
            <div class="form-group mb-3">
                <label for="email">Email address:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                <div class="invalid-feedback"></div>
            </div>
            <!-- Username -->
            <div class="form-group mb-3">
                <label for="username">Create a username:</label>
                <input type="text" id="username" name="username" class="form-control" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                <div class="invalid-feedback"></div>
            </div>
            <!-- Password -->
            <div class="form-group mb-3">
                <label for="password">Enter a password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <small>(At least 8 characters, including uppercase, lowercase, number, and special character)</small>
                <div class="invalid-feedback"></div>
            </div>
            <!-- Confirm password -->
            <div class="form-group mb-4">
                <label for="confirm_password">Confirm password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                <div class="invalid-feedback"></div>
            </div>
            <!-- Button to trigger the modal (not a submit button) -->
            <button type="button" class="btn btn-primary" id="reviewDetailsBtn-reg">Review Details</button>
            <br>
            <!-- Link to login page for previously registered users -->
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </form>
        <!-- Confirmation Modal -->
        <div class="modal fade" id="confirmationModal-reg" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmationModalLabel">Confirm Registration Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Please review your details before submitting:</p>
                        <!-- Display user details for confirmation -->
                        <ul>
                            <li><strong>Full Name:</strong> <span id="confirmFullName"></span></li>
                            <li><strong>Email:</strong> <span id="confirmEmail"></span></li>
                            <li><strong>Username:</strong> <span id="confirmUsername"></span></li>
                            <li><strong>Password:</strong> <span id="confirmPassword"></span></li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <!-- Buttons to cancel or confirm submission -->
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmSubmitBtn-reg">Confirm and Register</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer file
include 'includes/footer.php';
?>