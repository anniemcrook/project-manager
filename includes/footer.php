<?php

/**
 * File: footer.php
 * Renders the global site footer.
 * Loads Bootstrap and jQuery dependencies.
 * Loads the main JavaScript file for the site.
 */
?>

<!-- Footer section -->
<footer class="footer d-flex justify-content-between align-items-center">
    <p class="m-0">&copy; 2025 Project Manager Website by Annie Crook SUN: 250291988</p>
    <p class="m-0">Contact us at support@projectmanager.com</p>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- JavaScript -->
<script defer src="script.js?v=<?php echo time(); ?>"></script>

</body>
</html>