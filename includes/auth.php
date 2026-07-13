<?php
// Sessions remember the logged-in user and one-time messages between pages.
session_start();

// Save a message in the session so it appears after a page redirect.
function set_flash_message($type, $message, $duration = 10000) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'warning', 'error'
        'message' => $message,
        'duration' => (int) $duration
    ];
}

// Display the saved message once, then remove it from the session.
function display_flash_message() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        $duration = (int) ($_SESSION['flash']['duration'] ?? 10000);
        
        // Select the banner color that matches the message type.
        $css_class = '';
        if ($type === 'success') $css_class = 'flash-success';
        if ($type === 'warning') $css_class = 'flash-warning';
        if ($type === 'error') $css_class = 'flash-error';
        
        echo "<div class='flash-banner $css_class' id='flashBanner' data-duration='$duration'>
                <div class='flash-content'>$message</div>
                <button type='button' class='flash-close' id='flashClose' aria-label='Close message'>&times;</button>
              </div>";
        
        unset($_SESSION['flash']);
    }
}

// Redirect visitors who are not logged in as students.
function require_student_login() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
        header("Location: ../login.php");
        exit;
    }
}

// Redirect visitors who are not logged in as administrators.
function require_admin_login() {
    if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
    }
}

function redirect_if_logged_in() {
    // Send an existing user directly to the correct role dashboard.
    if (isset($_SESSION['admin_id']) && ($_SESSION['role'] ?? '') === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    }

    if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'student') {
        header("Location: student/dashboard.php");
        exit;
    }
}

// Prevent a logged-in student from reopening student login pages.
function redirect_if_student_logged_in() {
    if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'student') {
        header("Location: ../student/dashboard.php");
        exit;
    }
}

// Prevent a logged-in administrator from reopening admin login pages.
function redirect_if_admin_logged_in() {
    if (isset($_SESSION['admin_id']) && $_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
        exit;
    }
}
?>

