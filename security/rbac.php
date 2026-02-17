<?php
// ============================================================
// rbac.php - Role-Based Access Control
// ============================================================
// This file controls WHO can do WHAT.
// It's based directly on your Permission_Matrix (WIP).xlsx
//
// HOW TO USE ON ANY PAGE:
//   require_once '../security/rbac.php';
//   requireRole(['Administrator', 'Office Manager']); // Only these roles can see this page
//
// HOW TO CHECK A SPECIFIC PERMISSION:
//   if (canDo('visit_notes', 'add')) {
//       // Show the "Add Note" button
//   }
// ============================================================

require_once __DIR__ . '/auth.php';

// ============================================================
// PERMISSION MATRIX
// Copied directly from your Permission_Matrix (WIP).xlsx
//
// Format:
//   'Role Name' => [
//       'resource' => ['allowed', 'actions'],
//   ]
// ============================================================
$ROLE_PERMISSIONS = [

    'Administrator' => [
        'patient'            => ['view', 'add', 'edit', 'delete'],
        'schedule'           => ['view', 'add', 'edit', 'delete'],
        'visit_notes'        => ['view', 'add', 'edit', 'delete'],
        'users'              => ['view', 'add', 'edit', 'delete'],
        'insurance_info'     => ['view', 'add', 'edit', 'delete'],
        'emergency_contact'  => ['view', 'add', 'edit', 'delete'],
        'audit_log'          => ['view'],            // Admin can VIEW but not edit audit logs
        'permissions'        => ['view', 'add', 'edit'],
        'user_login_info'    => ['view', 'add', 'edit'],
        'nurse_assignments'  => ['view', 'add', 'edit'],
    ],

    'Doctor' => [
        'patient'            => ['view'],
        'schedule'           => ['view'],
        'visit_notes'        => ['view', 'add'],     // Can add notes, not edit others
        'nurse_assignments'  => ['view'],
        // NOTE: Medical info (visit_notes) is assigned patients only - enforce in queries
    ],

    'Nurse' => [
        'patient'            => ['view'],
        'schedule'           => ['view'],
        'visit_notes'        => ['view', 'add'],
        'nurse_assignments'  => ['view'],
        // NOTE: Same as Doctor - assigned patients only
    ],

    'Office Manager' => [
        'patient'            => ['view'],
        'schedule'           => ['view', 'add', 'edit'],
        'users'              => ['view', 'add', 'edit'],
        'insurance_info'     => ['view', 'add', 'edit'],
        'emergency_contact'  => ['view', 'add', 'edit'],
    ],

    'Receptionist' => [
        'patient'            => ['view', 'add', 'edit'],
        'schedule'           => ['view', 'add', 'edit'],
        'insurance_info'     => ['view', 'add', 'edit'],
        'emergency_contact'  => ['view', 'add', 'edit'],
    ],
];

// ============================================================
// requireRole($allowedRoles)
// Put at the top of any page to restrict who can see it.
// $allowedRoles is an array of role names that ARE allowed.
//
// Example - only admins:
//   requireRole(['Administrator']);
//
// Example - admins and office managers:
//   requireRole(['Administrator', 'Office Manager']);
// ============================================================
function requireRole($allowedRoles) {
    requireLogin(); // Must be logged in first

    $user = getCurrentUser();

    if (!in_array($user['role'], $allowedRoles)) {
        // Log the denied attempt
        logSecurityEvent(
            'ACCESS_DENIED',
            $user['id'],
            "Role '{$user['role']}' tried to access a page restricted to: " . implode(', ', $allowedRoles)
        );

        // Send them somewhere safe
        http_response_code(403);
        die("Access Denied: You don't have permission to view this page.");
        // TODO: Replace die() with a redirect to a nice "403" error page
    }
}

// ============================================================
// canDo($resource, $action)
// Check if the logged-in user can perform a specific action.
// Returns true or false.
//
// Use this to show/hide buttons and sections in your HTML.
//
// Example:
//   if (canDo('patient', 'edit')) {
//       echo '<a href="edit_patient.php">Edit Patient</a>';
//   }
// ============================================================
function canDo($resource, $action) {
    global $ROLE_PERMISSIONS;

    $user = getCurrentUser();
    if (!$user) return false;

    $role = $user['role'];

    // Check if this role has any permissions for this resource
    if (!isset($ROLE_PERMISSIONS[$role][$resource])) {
        return false;
    }

    // Check if the specific action is allowed
    return in_array($action, $ROLE_PERMISSIONS[$role][$resource]);
}

// ============================================================
// getRolePermissions($role)
// Returns all permissions for a given role.
// Useful for admin pages that display permission tables.
// ============================================================
function getRolePermissions($role) {
    global $ROLE_PERMISSIONS;
    return $ROLE_PERMISSIONS[$role] ?? [];
}
?>
