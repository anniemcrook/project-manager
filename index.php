<?php

/**
 * File: index.php
 * The main home page for the website.
 * This page has two versions - one for guests and one for logged in users.
 * The page will display different depending on login status.
 * 
 * @author Annie Crook 250291988
 */

// Start the session to manage user authentication state
session_start();

// If logged in, fetch user data; otherwise skip
$isLoggedIn = !empty($_SESSION['uid']);

// Include the header file (layout, navigation, bootstrap, etc.)
include 'includes/header.php';
?>

<!-- Main page content -->
<div class="page-content">
    <h1>Project Manager</h1><br><br>
    <h2>All your projects in one place</h2><br>

    <?php
    /**
     * Home page has two versions:
     * - Logged-in users: personal tools + welcome message
     * - Guests: marketing content + call to register
     */
    if ($isLoggedIn) {
        // Logged in
    ?>
        <!-- General website information -->
        <p>Welcome to Project Manager. Search our projects or add and update your own projects with ease.</p><br>
        <p><i>We make project management easy.</i></p><br>
        <!-- User specific welcome and links to profile and user projects -->
        <p><strong>Welcome back, <?php echo htmlspecialchars($_SESSION['firstname']); ?>!</strong></p>
        <div class="button-container">
            <?php
            echo '<a href="myprojects.php" class="btn btn-primary">View My Projects</a>';
            echo '<a href="profile.php" class="btn btn-secondary">View My Profile</a>';
            ?>
        </div>
    <?php
    } else {
        // Guests
    ?>
        <!-- General website information and how to register -->
        <p>Welcome to Project Manager. Search our projects or log in to add and update your own projects with ease.</p><br>
        <p>Register with us to view, edit, and add your own projects. Just click the link, fill
            in your details, and get immediate access to all features. </p>
        <p><i>We make project management easy.</i></p>
        <!-- Call to log in or register -->
        <?php echo "<p>Please log in or register to access your personal dashboard.</p>"; ?>
        <div class="button-container">
            <?php
            echo '<a href="login.php" class="btn btn-primary">Log In</a>';
            echo '<a href="register.php" class="btn btn-secondary">Register</a>';
            ?>
        </div>

        <!--Section for reasons to sign up-->
        <section id="benefits">
            <br><br>
            <h2>Why Register With Us?</h2>
            <div class="benefit">
                <p>Manage your projects efficiently.</p>
            </div>
            <div class="benefit">
                <p>Share your projects with others.</p>
            </div>
            <div class="benefit">
                <p>Build your network with other developers.</p>
            </div>
        </section>
        <!-- Section for user testimonials -->
        <section id="testimonials">
            <br><br>
            <h2>What our users say about us:</h2>
            <blockquote>
                <p>"Project Manager has made managing my workload much easier."</p>
                <footer>- Mary</footer>
            </blockquote>
            <blockquote>
                <p>"It really motivated me seeing other user's projects."</p>
                <footer>- John</footer>
            </blockquote>
        </section>
    <?php
    }
    ?>
</div>

<?php
// Include the footer file
include 'includes/footer.php';
?>