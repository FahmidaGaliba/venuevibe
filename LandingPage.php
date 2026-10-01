<?php
/* ============================================================
   VenueVibe - LandingPage.php
   Landing page PHP entry point with session awareness
   ============================================================ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Render the landing page
include 'LandingPage.html';
?>
