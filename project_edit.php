<?php

/**
 * File: project_edit.php
 * Allows logged-in users to update their existing projects.
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

// Get the project ID from URL or Post from form submission
$pid = $_GET['pid'] ?? $_POST['pid'] ?? null;

if (!$pid) {
    echo "No project selected.";
    exit();
}

// Fetch existing project (must belong to logged-in user)
try {
    // Fetch data for project edit
    $stmt = $db->prepare("SELECT * FROM projects WHERE pid = ? AND uid = ?");
    $stmt->execute([$pid, $userid]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        echo "Project not found or access denied.";
        exit();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "Error loading project details.";
    exit();
}

// Handle form submission
$errors = [];
$success = '';

if (isset($_POST['submit'])) {

    // CSRF protection
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    // Retrieve and sanitize form inputs
    $pid                = intval($_POST['pid']);
    $title              = trim($_POST['title']);
    $short_description  = trim($_POST['short_description']);
    $start_date         = trim($_POST['start_date']);
    $end_date           = trim($_POST['end_date']);
    $phase              = $_POST['phase'];

    // Validation
    if ($title === '' || $start_date === '' || $short_description === '' || $phase === '') {
        $errors[] = "Please fill in all required fields.";
    }
    // Make sure end date is not earlier than start date
    if ($end_date !== '' && $end_date < $start_date) {
        $errors[] = "End date cannot be earlier than start date.";
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE projects 
                    SET title = :title,
                        start_date = :start_date,
                        end_date = :end_date,
                        short_description = :short_description,
                        phase = :phase
                    WHERE pid = :pid AND uid = :uid";

            $stmt = $db->prepare($sql);

            $endDateParam = $end_date !== '' ? $end_date : null;

            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $endDateParam);
            $stmt->bindParam(':short_description', $short_description);
            $stmt->bindParam(':phase', $phase);
            $stmt->bindParam(':pid', $pid, PDO::PARAM_INT);
            $stmt->bindParam(':uid', $userid, PDO::PARAM_INT);

            $stmt->execute();

            $success = "Project updated successfully!";
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
        <!-- Form for editing existing project -->
        <form method="POST" action="project_edit.php" id="edit-project-form" novalidate>
            <h2>Edit Project</h2>
            <!-- Hidden field to detect form submission -->
            <input type="hidden" name="submit" value="1">
            <input type="hidden" name="pid" value="<?php echo htmlspecialchars($project['pid']); ?>">
            <!-- CSRF token for security -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <!-- New project title -->
            <div class="form-group mb-3">
                <label for="title">Project Title:</label>
                <input type="text" id="title-edit" name="title" class="form-control"
                    value="<?php echo htmlspecialchars($project['title']); ?>" required>
                <div class="invalid-feedback"></div>
            </div>
            <!-- Short description -->
            <div class="form-group mb-3">
                <label for="short_description">Short Description:</label>
                <textarea id="short_description-edit" name="short_description" class="form-control" required>
                    <?php echo htmlspecialchars($project['short_description']); ?>
                </textarea>
                <div class="invalid-feedback"></div>
            </div>
            <!-- Start date and end date-->
            <div class="form-row">
                <!-- Start date -->
                <div class="col-md-6 mb-3">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date-edit" name="start_date" class="form-control"
                        value="<?php echo htmlspecialchars($project['start_date']); ?>" required>
                    <div class="invalid-feedback"></div>
                </div>
                <!-- end date -->
                <div class="col-md-6 mb-3">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date-edit" name="end_date" class="form-control"
                        value="<?php echo htmlspecialchars($project['end_date'] ?? ''); ?>">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <!-- Project phase (drop down selector) -->
            <div class="form-group mb-3">
                <label for="phase">Phase:</label>
                <select id="phase-edit" name="phase" class="form-control" required>
                    <option value="design" <?php if ($project['phase'] == 'design') echo 'selected'; ?>>Design</option>
                    <option value="development" <?php if ($project['phase'] == 'development') echo 'selected'; ?>>Development</option>
                    <option value="testing" <?php if ($project['phase'] == 'testing') echo 'selected'; ?>>Testing</option>
                    <option value="deployment" <?php if ($project['phase'] == 'deployment') echo 'selected'; ?>>Deployment</option>
                    <option value="complete" <?php if ($project['phase'] == 'complete') echo 'selected'; ?>>Complete</option>
                </select>
            </div>
            <!-- Button to trigger the modal (not a submit button) -->
            <button type="button" class="btn btn-primary" id="reviewDetailsBtn-edit">Review Details</button>
            <br>
            <!-- Return to My Projects page link -->
            <p>Return to<a href="myprojects.php"> My Projects</a></p>
        </form>
        <!-- Confirmation Modal -->
        <div class="modal fade" id="confirmationModal-edit" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
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
                            <li><strong>Title:</strong> <span id="confirmTitleEdit"></span></li>
                            <li><strong>Description:</strong> <span id="confirmShortDescriptionEdit"></span></li>
                            <li><strong>Start Date:</strong> <span id="confirmStartDateEdit"></span></li>
                            <li><strong>End Date:</strong> <span id="confirmEndDateEdit"></span></li>
                            <li><strong>Current Phase:</strong> <span id="confirmPhaseEdit"></span></li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <!-- Buttons to cancel or confirm submission -->
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmSubmitBtn-edit">Confirm Changes</button>
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