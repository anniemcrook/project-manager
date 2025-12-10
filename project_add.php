<?php

/**
 * File: project_add.php
 * Handles adding new projects to the system.
 * Takes user input and adds new project details to the database.
 */

// Start the session to manage user authentication state
session_start();

// Enforce authentication — redirect to login if user is not logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

// Include database connection file
include 'connectdb.php';

// Prepare for user input
$errors = [];
$success = '';

if (isset($_POST['submit'])) {

    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    // Assign form values
    $uid               = $_SESSION['uid'];
    $title             = trim($_POST['title']);
    $short_description = trim($_POST['short_description']);
    $start_date        = trim($_POST['start_date']);
    $end_date          = trim($_POST['end_date']);
    $phase             = $_POST['phase'];

    // Required field validation
    if ($title === '' || $start_date === '' || $short_description === '' || $phase === '') {
        $errors[] = "Please fill in all required fields.";
    }
    // Limit input length for title and short description
    if (strlen($title) > 255) {
        $errors[] = "Title must be less than 255 characters.";
    }
    if (strlen($short_description) > 1000) {
        $errors[] = "Description must be under 1000 characters.";
    }
    // Make sure end date is not earlier than start date
    if ($end_date !== '' && $end_date < $start_date) {
        $errors[] = "End date cannot be earlier than start date.";
    }

    // If all fields entered correctly then insert into database
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO projects (title, start_date, end_date, short_description, phase, uid)
                    VALUES (:title, :start_date, :end_date, :short_description, :phase, :uid)";

            $stmt = $db->prepare($sql);

            $endDateParam = $end_date !== '' ? $end_date : null;

            // Link user input with database fields
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':start_date', $start_date);
            $stmt->bindValue(':end_date', $endDateParam);
            $stmt->bindValue(':short_description', $short_description);
            $stmt->bindValue(':phase', $phase);
            $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);

            $stmt->execute();

            $success = "Project added successfully!";
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Include the header file (layout, navigation, bootstrap, etc.)
include 'includes/header.php';
?>

<!-- Main page content -->
<div class="page-content">

    <!-- Error message -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>
    <!-- Success message -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <p><?php echo $success; ?></p>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <!-- Form for adding new project -->
        <form method="post" action="project_add.php" id="add-project-form" novalidate>
            <h2>Add a New Project</h2>

            <!-- CSRF token for security -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <!-- Hidden field to detect form submission -->
            <input type="hidden" name="submit" value="1">

            <!-- New project title -->
            <div class="form-group mb-3">
                <label for="title">Project Title:</label>
                <input type="text" id="title-add" name="title" class="form-control"
                    value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                <div class="invalid-feedback"></div>
            </div>
            <!-- Short description -->
            <div class="form-group mb-3">
                <label for="short_description">Short Description:</label>
                <textarea id="short_description-add" name="short_description" rows="4" class="form-control" required>
                    <?php echo isset($short_description) ? htmlspecialchars($short_description) : ''; ?>
                </textarea>
                <div class="invalid-feedback"></div>
            </div>
            <!-- Start date and end date-->
            <div class="form-row">
                <!-- Start date -->
                <div class="col-md-6 mb-3">
                    <label for="start_date">Project Start Date:</label>
                    <input type="date" id="start_date-add" name="start_date" class="form-control"
                        value="<?php echo isset($start_date) ? htmlspecialchars($start_date) : ''; ?>" required>
                    <div class="invalid-feedback"></div>
                </div>
                <!-- end date -->
                <div class="col-md-6 mb-3">
                    <label for="end_date">End Date (optional):</label>
                    <input type="date" id="end_date-add" name="end_date" class="form-control"
                        value="<?php echo isset($end_date) ? htmlspecialchars($end_date) : ''; ?>">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <!-- Current project phase (drop down selector) -->
            <div class="form-group mb-4">
                <label for="phase">Current Phase of the Project:</label>
                <select id="phase-add" name="phase" class="form-control" required>
                    <option value="design">Design</option>
                    <option value="development">Development</option>
                    <option value="testing">Testing</option>
                    <option value="deployment">Deployment</option>
                    <option value="complete">Complete</option>
                </select>
            </div>
            <!-- Button to trigger the modal (not a submit button) -->
            <button type="button" class="btn btn-primary" id="reviewDetailsBtn-add">Review Details</button>
            <br>
            <!-- Return to My Projects page link -->
            <p>Return to<a href="myprojects.php"> My Projects</a></p>
        </form>
        <!-- Confirmation Modal -->
        <div class="modal fade" id="confirmationModal-add" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmationModalLabel">Confirm Project Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Please review your details before submitting:</p>
                        <!-- Display project details for confirmation -->
                        <ul>
                            <li><strong>Title:</strong> <span id="confirmTitleAdd"></span></li>
                            <li><strong>Description:</strong> <span id="confirmShortDescriptionAdd"></span></li>
                            <li><strong>Start Date:</strong> <span id="confirmStartDateAdd"></span></li>
                            <li><strong>End Date:</strong> <span id="confirmEndDateAdd"></span></li>
                            <li><strong>Current Phase:</strong> <span id="confirmPhaseAdd"></span></li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <!-- Buttons to cancel or confirm submission -->   
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmSubmitBtn-add">Confirm and Add Project</button>
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