<?php
// ============================================================
// validation.php - Input Validation & Sanitization
// ============================================================
// This file protects against:
//   - SQL Injection (malicious database commands in form fields)
//   - XSS / Cross-Site Scripting (malicious HTML/JS in form fields)
//   - Invalid data formats (wrong date, phone number too long, etc.)
//
// HOW TO USE:
//   require_once '../security/validation.php';
//
//   // Clean a text field before displaying it
//   $name = sanitizeOutput($_POST['name']);
//
//   // Validate before saving to database
//   $errors = validatePatient($_POST);
//   if (!empty($errors)) { // show errors }
// ============================================================

// ============================================================
// sanitizeOutput($value)
// Use this when DISPLAYING user-supplied data in HTML.
// Converts <script> into &lt;script&gt; so it can't run.
//
// RULE: Any data that came from a user or the database
//       MUST go through this before being echo'd.
// ============================================================
function sanitizeOutput($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// ============================================================
// sanitizeInput($value)
// Basic cleanup for text inputs before using in logic.
// Does NOT replace sanitizeOutput - use both where needed.
// ============================================================
function sanitizeInput($value) {
    return trim(strip_tags($value ?? ''));
}

// ============================================================
// Individual field validators
// Each returns true if valid, false if not.
// ============================================================

function isValidName($value) {
    $clean = sanitizeInput($value);
    // Letters, spaces, hyphens, apostrophes only (for names like O'Brien or Mary-Jane)
    return strlen($clean) >= 1 && strlen($clean) <= 128 && preg_match("/^[a-zA-Z\s\-']+$/", $clean);
}

function isValidUsername($value) {
    $clean = sanitizeInput($value);
    // Letters, numbers, underscores, 3-50 chars
    return preg_match('/^[a-zA-Z0-9_]{3,50}$/', $clean);
}

function isValidPassword($value) {
    // Must be at least 12 characters
    // Must have: uppercase, lowercase, number, special character
    $hasUpper   = preg_match('/[A-Z]/', $value);
    $hasLower   = preg_match('/[a-z]/', $value);
    $hasNumber  = preg_match('/[0-9]/', $value);
    $hasSpecial = preg_match('/[^a-zA-Z0-9]/', $value);
    $longEnough = strlen($value) >= 12;

    return $hasUpper && $hasLower && $hasNumber && $hasSpecial && $longEnough;
}

function isValidPhone($value) {
    $clean = preg_replace('/\D/', '', $value); // Strip non-digits
    return strlen($clean) >= 10 && strlen($clean) <= 20;
}

function isValidEmail($value) {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false && strlen($value) <= 255;
}

function isValidDate($value) {
    // Expects YYYY-MM-DD format
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d && $d->format('Y-m-d') === $value;
}

function isValidDatetime($value) {
    // Expects YYYY-MM-DD HH:MM:SS format
    $d = DateTime::createFromFormat('Y-m-d H:i:s', $value);
    return $d && $d->format('Y-m-d H:i:s') === $value;
}

// ============================================================
// Form validators - validate a whole form at once
// Each returns an array of error messages (empty = all good)
// ============================================================

// Validate patient form fields (Patient table)
function validatePatient($data) {
    $errors = [];

    if (empty($data['First_Name']) || !isValidName($data['First_Name'])) {
        $errors[] = "First name is required and must contain only letters.";
    }
    if (empty($data['Last_Name']) || !isValidName($data['Last_Name'])) {
        $errors[] = "Last name is required and must contain only letters.";
    }
    if (!empty($data['Phone_Number']) && !isValidPhone($data['Phone_Number'])) {
        $errors[] = "Phone number format is invalid.";
    }
    if (!empty($data['Email']) && !isValidEmail($data['Email'])) {
        $errors[] = "Email address format is invalid.";
    }
    if (empty($data['Date_Of_Birth']) || !isValidDate($data['Date_Of_Birth'])) {
        $errors[] = "Date of birth is required (format: YYYY-MM-DD).";
    }

    return $errors;
}

// Validate login form fields
function validateLoginInput($data) {
    $errors = [];

    if (empty($data['username']) || !isValidUsername($data['username'])) {
        $errors[] = "Invalid username format.";
    }
    if (empty($data['password'])) {
        $errors[] = "Password is required.";
    }

    return $errors;
}

// Validate new user creation (User + User_Login_Info tables)
function validateNewUser($data) {
    $errors = [];

    if (empty($data['First_Name']) || !isValidName($data['First_Name'])) {
        $errors[] = "First name is required.";
    }
    if (empty($data['Last_Name']) || !isValidName($data['Last_Name'])) {
        $errors[] = "Last name is required.";
    }
    if (empty($data['username']) || !isValidUsername($data['username'])) {
        $errors[] = "Username must be 3-50 characters, letters/numbers/underscore only.";
    }
    if (empty($data['password']) || !isValidPassword($data['password'])) {
        $errors[] = "Password must be 12+ characters with uppercase, lowercase, number, and special character.";
    }
    if (!empty($data['Email']) && !isValidEmail($data['Email'])) {
        $errors[] = "Email format is invalid.";
    }

    return $errors;
}
?>
