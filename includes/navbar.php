<?php

/**
 * File: navbar.php
 * Renders the main navigation bar for the website
 * Displays user-specific links when logged in
 */
?>

<nav class="navbar navbar-expand-lg navbar-light">
    <a class="navbar-brand" href="index.php">Project Manager</a>

    <!-- Mobile Menu Toggle -->
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <!-- Collapsible Menu -->
    <div class="collapse navbar-collapse" id="navbarNav">
        <!-- Left-aligned links (always visible) -->
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="projects.php">View Projects</a>
            </li>
        </ul>
        <!-- Right-aligned links (depend on login state) -->
        <ul class="navbar-nav ml-auto">
            <?php if (!empty($_SESSION['uid'])): ?>
                <!-- Logged-in User Greeting -->
                <li class="nav-item">
                    <span class="nav-link">
                        <i>Welcome, <?php echo htmlspecialchars($_SESSION['firstname']); ?>!</i>
                    </span>
                </li>
                <!-- User-specific links -->
                <li class="nav-item">
                    <a class="nav-link" href="myprojects.php">My Projects</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>

            <?php else: ?>
                <!-- Guest links -->
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">Register</a>
                </li>
            <?php endif; ?>
            
        </ul>
    </div>
</nav>