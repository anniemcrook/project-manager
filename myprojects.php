<?php

/**
 * File: myprojects.php
 * Displays the list of projects for the authenticated user.
 * The page ensures that only authenticated users can access it, fetches their
 * projects securely using prepared statements, and handles database errors.
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

// Fetch user profile information from the database
try {
    // Retrieve full user details based on the user ID stored in the session
    $stmt = $db->prepare("SELECT * FROM users WHERE uid = ?");
    $stmt->execute([$_SESSION['uid']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error for debugging (not exposed to user)
    error_log($e->getMessage());
    // Display a generic, user-friendly message
    echo "Sorry, we're having technical issues. Please try again later.";
    $user = null;
}

// Include the header file (layout, navigation, bootstrap, etc.)
include 'includes/header.php';

// Success message
if (isset($_GET['deleted'])) {
    echo "<p style='color:green; text-align:center;'>Project deleted successfully.</p>";
}

?>

<!-- Main page content -->
<div class="page-content">
    <h2>My Projects</h2>
    <div class="projects-list">
        <?php
        // Fetch projects for the logged-in user from the database
        try {
            $stmt = $db->prepare("SELECT pid, title, short_description, start_date, end_date, phase, updated_at 
                                    FROM projects 
                                    WHERE uid = ? 
                                    ORDER BY start_date DESC");
            $stmt->execute([$_SESSION['uid']]);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error for debugging (not exposed to user)
            error_log($e->getMessage());
            // Display a generic, user-friendly message
            echo "<p>Sorry, we're having technical issues. Please try again later.</p>";
            $projects = [];
        }
        ?>
        <!-- Display the list of user's projects on cards -->
        <div class="container-fluid mt-4">
            <div class="container">
                <div class="row">
                    <!-- Check if projects exist -->
                    <?php if (count($projects) > 0): ?>
                        <?php foreach ($projects as $project): ?>
                            <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 project-item">
                                    <div class="card-body">
                                        <!-- Display project title, short description, start date, and creation date -->
                                        <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($project['short_description'])); ?></p>
                                        <p><strong>Start Date:</strong> <?php echo htmlspecialchars($project['start_date']); ?></p>
                                        <p><strong>End Date:</strong> <?php echo htmlspecialchars($project['end_date']); ?></p>
                                        <p><strong>Current Phase:</strong> <?php echo htmlspecialchars($project['phase']); ?></p>
                                    </div>
                                    <div class="card-footer">
                                        <!-- Edit and Delete buttons -->
                                        <a href="project_edit.php?pid=<?php echo htmlspecialchars($project['pid']); ?>" class="btn btn-secondary">
                                            Edit
                                        </a>
                                        <button type="button"
                                            class="btn btn-danger deleteBtn"
                                            data-toggle="modal"
                                            data-target="#deleteModal"
                                            data-delete-url="project_delete.php?pid=<?php echo $project['pid']; ?>&csrf=<?php echo $_SESSION['csrf_token']; ?>">
                                            Delete
                                        </button>
                                        <!-- Project details most recent update -->
                                        <small class="text-muted d-block mt-2">
                                            Last updated <?php echo htmlspecialchars($project['updated_at']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p class="col-12 text-center">You have no projects.</p>
                    <?php endif; ?>
                </div>
                <!-- Add a new project button -->
                <div class="text-center my-4">
                    <a href="project_add.php" class="btn btn-primary">Add New Project</a>
                </div>
            </div>
        </div>
        <!-- Delete project confirmation modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this project? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
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