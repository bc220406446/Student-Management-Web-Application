<?php
session_start();

// Flash message functions
function set_flash_message($type, $message, $duration = 10000) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'warning', 'error'
        'message' => $message,
        'duration' => (int) $duration
    ];
}

function display_flash_message() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        $duration = (int) ($_SESSION['flash']['duration'] ?? 10000);
        
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

// Student authentication
function require_student_login() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
        header("Location: ../student/login.php");
        exit;
    }
}

// Admin authentication
function require_admin_login() {
    if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../admin/login.php");
        exit;
    }
}

// Check if already logged in as student (for login/register pages)
function redirect_if_student_logged_in() {
    if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'student') {
        header("Location: ../student/dashboard.php");
        exit;
    }
}

// Check if already logged in as admin (for admin login page)
function redirect_if_admin_logged_in() {
    if (isset($_SESSION['admin_id']) && $_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
        exit;
    }
}
?>

