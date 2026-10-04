<?php
/**
 * Shared session, messaging and access-control helpers.
 *
 * Every page loads this file instead of implementing its own authentication
 * logic, so login, logout and role based access are defined in one place.
 */

// Sessions remember the logged-in user and one-time messages between pages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// The roles this application recognises and where each one lands after login.
const ROLE_ADMIN = 'admin';
const ROLE_STUDENT = 'student';

// Every authentication action is served by the single auth.php entry point.
const AUTH_ENTRY = 'auth.php';

/*
   Flash messages
   A message is stored in the session so it survives a page redirect and is
   shown exactly once on the next page that renders it.
*/

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
    if (!isset($_SESSION['flash'])) {
        return;
    }

    $type = $_SESSION['flash']['type'];
    $message = $_SESSION['flash']['message'];
    $duration = (int) ($_SESSION['flash']['duration'] ?? 10000);

    // Select the banner color that matches the message type.
    $css_class = '';
    if ($type === 'success')
        $css_class = 'flash-success';
    if ($type === 'warning')
        $css_class = 'flash-warning';
    if ($type === 'error')
        $css_class = 'flash-error';

    echo "<div class='flash-banner $css_class' id='flashBanner' data-duration='$duration'>
                <div class='flash-content'>$message</div>
                <button type='button' class='flash-close' id='flashClose' aria-label='Close message'>&times;</button>
              </div>";

    unset($_SESSION['flash']);
}

/*
   Identity and roles
   Both roles share the same session keys, so a page only ever needs to read
   $_SESSION['role'] to decide what the visitor is allowed to do.
*/

// Return the signed-in role, or null when nobody is signed in.
function current_role() {
    return $_SESSION['role'] ?? null;
}

// True while a visitor holds a valid role.
function is_logged_in() {
    return current_role() !== null;
}

// The dashboard a role is sent to, as a path relative to the site root.
function dashboard_path($role) {
    return $role === ROLE_ADMIN ? 'admin/dashboard.php' : 'student/dashboard.php';
}

// Sign a user in by storing one shared set of identity keys in the session.
function login_user($role, $id, $name, $email) {
    // A new session id prevents session fixation after a successful sign in.
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
    $_SESSION['role'] = $role;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
}

// Clear every trace of the current session and show a confirmation message.
function logout_user() {
    $_SESSION = [];

    // Also expire the session cookie so the browser stops sending the id.
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();

    // Start a fresh session so the confirmation message has somewhere to live.
    session_start();
    set_flash_message('success', 'You have been logged out.');
}

// Look up an account by email across both role tables.
// Returns ['role' => ..., 'id' => ..., 'name' => ..., 'row' => ...] or null.
function find_account_by_email($pdo, $email) {
    // Administrators are checked first so a role clash resolves predictably.
    $stmt = $pdo->prepare('SELECT AdminID, AdminName, Email, Password FROM admin WHERE Email = ?');
    $stmt->execute([$email]);
    if ($admin = $stmt->fetch()) {
        return [
            'role' => ROLE_ADMIN,
            'id' => $admin['AdminID'],
            'name' => $admin['AdminName'],
            'row' => $admin
        ];
    }

    $stmt = $pdo->prepare('SELECT StudentID, Name, Email, Password, Status FROM student WHERE Email = ?');
    $stmt->execute([$email]);
    if ($student = $stmt->fetch()) {
        return [
            'role' => ROLE_STUDENT,
            'id' => $student['StudentID'],
            'name' => $student['Name'],
            'row' => $student
        ];
    }

    return null;
}

// True when the email is already used by either an admin or a student account.
function email_is_taken($pdo, $email, $except_student_id = null) {
    $stmt = $pdo->prepare('SELECT Email FROM admin WHERE Email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return true;
    }

    if ($except_student_id !== null) {
        // Skip the signed-in student so they can keep their own address.
        $stmt = $pdo->prepare('SELECT Email FROM student WHERE Email = ? AND StudentID <> ?');
        $stmt->execute([$email, $except_student_id]);
    } else {
        $stmt = $pdo->prepare('SELECT Email FROM student WHERE Email = ?');
        $stmt->execute([$email]);
    }

    return (bool) $stmt->fetch();
}

/*
   Role based access control
   Pages call require_role() instead of checking the session themselves, so a
   role can never reach a page that belongs to the other role.
*/

// Allow the page to continue only for the given role.
// Call from admin/ and student/ pages; '../' returns to the site root.
function require_role($role) {
    if (current_role() === $role) {
        return;
    }

    if (is_logged_in()) {
        // Signed in with the wrong role: send them to their own dashboard.
        header('Location: ../' . dashboard_path(current_role()));
    } else {
        // Signed out: send them to the login form.
        header('Location: ../' . AUTH_ENTRY);
    }
    exit;
}

// Send an existing user directly to the correct role dashboard.
// Call from the site root, where dashboard paths are already relative.
function redirect_if_logged_in() {
    if (is_logged_in()) {
        header('Location: ' . dashboard_path(current_role()));
        exit;
    }
}

// Stop a logged-in user from opening the authentication screens again.
function redirect_if_authenticated() {
    if (is_logged_in()) {
        header('Location: ' . dashboard_path(current_role()));
        exit;
    }
}