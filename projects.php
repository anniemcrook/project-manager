<?php

/**
 * File: projects.php
 * Displays the list of projects for all users.
 * The page fetches projects securely using prepared statements,
 * and handles database errors gracefully.
 */

// Start the session to manage user authentication state
session_start();

// Include the database connection file
include 'connectdb.php';

// Include the header file (layout, navigation, bootstrap, etc.)
include 'includes/header.php';
?>

<!-- Main page content -->
<div class="page-content">
    <h2>View Projects</h2>
    <!-- Users can search single or multiple fields combined -->
    <p>Search our projects using the search form below.</p>
    <!-- Collapsible Project Search Form -->
    <p>
        <!-- Expand and collapse search form button -->
        <button class="btn btn-primary" type="button"
            data-toggle="collapse"
            data-target="#collapseSearch"
            aria-expanded="false"
            aria-controls="collapseSearch">
            Search Projects
        </button>
    </p>
    <div class="collapse" id="collapseSearch">
        <div class="card card-body">
            <div class="form-container">
                <form method="get" action="projects.php" class="search-form">
                    <h3>Search</h3>
                    <!-- Search by title -->
                    <label for="searchTitle">Search by project title:</label>
                    <input type="text" name="searchTitle" class="form-control"
                        value="<?php echo isset($_GET['searchTitle']) ? htmlspecialchars($_GET['searchTitle']) : ''; ?>">
                    <!-- Search by username -->
                    <label for="searchUsername">Search by username:</label>
                    <input type="text" name="searchUsername" class="form-control"
                        value="<?php echo isset($_GET['searchUsername']) ? htmlspecialchars($_GET['searchUsername']) : ''; ?>">
                    <!-- Search by start date, returning results of that date or later -->
                    <label for="searchStartDate">Show projects starting on or after this date:</label>
                    <input type="date" name="searchStartDate" class="form-control"
                        value="<?php echo isset($_GET['searchStartDate']) ? htmlspecialchars($_GET['searchStartDate']) : ''; ?>">
                    <!-- Search by project phase -->
                    <label for="searchPhase">Search by phase:</label>
                    <select id="searchPhase" name="searchPhase" class="form-control">
                        <option value="">-- Any Phase --</option>
                        <option value="design" <?php if (isset($_GET['searchPhase']) && $_GET['searchPhase'] == 'design') echo 'selected'; ?>>Design</option>
                        <option value="development" <?php if (isset($_GET['searchPhase']) && $_GET['searchPhase'] == 'development') echo 'selected'; ?>>Development</option>
                        <option value="testing" <?php if (isset($_GET['searchPhase']) && $_GET['searchPhase'] == 'testing') echo 'selected'; ?>>Testing</option>
                        <option value="deployment" <?php if (isset($_GET['searchPhase']) && $_GET['searchPhase'] == 'deployment') echo 'selected'; ?>>Deployment</option>
                        <option value="complete" <?php if (isset($_GET['searchPhase']) && $_GET['searchPhase'] == 'complete') echo 'selected'; ?>>Complete</option>
                    </select>
                    <div class="d-flex gap-2 mt-3">
                        <!-- Submit button -->
                        <input type="submit" class="btn btn-primary" value="Search">
                        <!-- Reset search filters -->
                        <a href="projects.php" class="btn btn-secondary">Reset Filters</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    // Collect user input
    $titleSearch = trim($_GET['searchTitle'] ?? '');
    $userSearch  = trim($_GET['searchUsername'] ?? '');
    $phaseSearch = trim($_GET['searchPhase'] ?? '');
    $dateSearch = trim($_GET['searchStartDate'] ?? '');

    ?>

    <div class="projects-list">
        <?php
        // Dynamic Search Query
        $query = "
            SELECT 
                p.pid, p.uid, p.title, p.short_description, p.start_date, p.end_date,
                p.phase, p.created_at AS project_created, p.updated_at AS project_updated,
                u.username, u.email 
            FROM projects p 
            JOIN users u ON p.uid = u.uid
        ";

        $conditions = [];
        $params = [];

        // Title filter
        if (!empty($titleSearch)) {
            $conditions[] = "p.title LIKE :title";
            $params[':title'] = "%" . $titleSearch . "%";
        }
        // Username filter
        if (!empty($userSearch)) {
            $conditions[] = "u.username LIKE :username";
            $params[':username'] = "%" . $userSearch . "%";
        }
        // Phase filter
        if (!empty($phaseSearch)) {
            $conditions[] = "p.phase = :phase";
            $params[':phase'] = $phaseSearch;
        }
        // Start Date filter
        if (!empty($dateSearch)) {
            $conditions[] = "p.start_date >= :start_date";
            $params[':start_date'] = $dateSearch;
        }
        // If we have conditions, add WHERE
        if ($conditions) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        // Always order results
        $query .= " ORDER BY p.created_at DESC";
        // Execute final query
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo "<p>Sorry, we're having technical issues. Please try again later.</p>";
            $projects = [];
        }
        ?>
        <!-- Display number of results -->
        <p><strong><?php echo count($projects); ?></strong> project(s) found.</p>
        <!-- Display the list of projects as individual cards -->
        <div class="container-fluid mt-4">
            <div class="container">
                <div class="row">
                    <?php if (count($projects) > 0): ?>
                        <?php foreach ($projects as $project): ?>
                            <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 project-item">
                                    <div class="card-body">
                                        <!-- Project title -->
                                        <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                        <!-- Short description -->
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($project['short_description'])); ?></p>
                                        <!-- Start date -->
                                        <p><strong>Start Date:</strong> <?php echo htmlspecialchars($project['start_date']); ?></p>
                                        <?php if (isset($_SESSION['username'])): ?>
                                            <!-- Only display full username if logged in user -->
                                            <p><strong>Owner:</strong> <?php echo htmlspecialchars($project['username']); ?></p>
                                        <?php else: ?>
                                            <!-- Display a redacted username for guest users -->
                                            <p><strong>Owner:</strong>
                                                <?php
                                                $public_name = substr($project['username'], 0, 1) . str_repeat('*', strlen($project['username']) - 1);
                                                echo htmlspecialchars($public_name);
                                                ?></p>
                                        <?php endif; ?>
                                        <!-- Modal button to view more details about the project in a pop-up -->
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#projectModal-<?php echo $project['pid']; ?>">
                                            View more details
                                        </button>
                                        <!-- Modal -->
                                        <div class="modal fade" id="projectModal-<?php echo $project['pid']; ?>" tabindex="-1"
                                            aria-labelledby="projectModalLabel-<?php echo $project['pid']; ?>" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <!-- Selected project title in Modal title -->
                                                        <h5 class="modal-title" id="projectModalLabel-<?php echo $project['pid']; ?>">
                                                            <?php echo htmlspecialchars($project['title']); ?></h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- More details: end date, current phase, and user email -->
                                                        <p><strong>End Date:</strong> <?php echo htmlspecialchars($project['end_date']); ?></p>
                                                        <p><strong>Current Phase:</strong> <?php echo htmlspecialchars($project['phase']); ?></p>
                                                        <p><strong>Contact Email:</strong> <?php echo htmlspecialchars($project['email']); ?></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <!-- Most recent update -->
                                        <small class="text-muted">
                                            Last updated <?php echo htmlspecialchars($project['project_updated']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="col-12">No projects found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer file
include 'includes/footer.php';
?>