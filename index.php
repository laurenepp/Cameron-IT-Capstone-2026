<?php
// Start or resume the user session
// Sessions allow us to store login data later (user id, role, etc.)
session_start();

/*
  LOGIN PAGE PURPOSE
  ------------------
  - Displays login form UI
  - Accepts POST submission
  - Validates basic input
  - Shows error if login fails
  - Later this will connect to database
*/

// Variable to hold any error message
$error = "";

// Check if the form was submitted
// $_SERVER['REQUEST_METHOD'] tells us how the page was accessed
// POST means user clicked Sign In
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get username from form safely
    // ?? '' means if it doesn't exist, use empty string
    // trim() removes extra spaces
    $username = trim($_POST['username'] ?? '');

    // Get password (do not trim passwords normally)
    $password = $_POST['password'] ?? '';

    // Validate fields are not empty
    if ($username === '' || $password === '') {
        // Set error message if user left fields blank
        $error = "Invalid username or password.";
    } else {
        // TEMPORARY MESSAGE — no database yet
        // Later this block will check credentials against MySQL
        $error = "Login is not connected to the database yet.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Character encoding -->
    <meta charset="UTF-8" />

    <!-- Mobile responsive scaling -->
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Browser tab title -->
    <title>Staff Portal - Sign In</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">

    <!-- Link external stylesheet -->
    <!-- ../ moves up one folder from /public to /assets -->
    <link rel="stylesheet" href="css/index.css">
</head>

<body>

    <!-- Main page wrapper -->
    <main class="page">

    <!-- Clinic logo -->
    <div class="logo-container">
    <img src="assets/images/Final Family Desert 1.png" alt="Riverside Family Clinic Logo" class="logo">
    </div>


        <!-- Page heading -->
        <h1 class="page-title">
            Staff Portal - Sign in to access the management system
        </h1>

        <!-- Login card container -->
        <section class="card" aria-label="Sign in">

            <!-- Show error message if one exists -->
            <?php if ($error): ?>
                <div class="alert" role="alert">
                    <!-- htmlspecialchars prevents XSS injection -->
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Login form -->
            <!-- method POST = secure form submission -->
            <!-- action index.php = submit back to same page -->
            <form method="post" action="index.php" autocomplete="off" novalidate>

                <!-- Username label -->
                <label class="label" for="username">Username</label>

                <!-- Input wrapper with icon -->
                <div class="field">

                    <!-- Icon container -->
                    <span class="icon" aria-hidden="true">

                        <!-- SVG user icon -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21a8 8 0 0 0-16 0"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>

                    </span>

                    <!-- Username input -->
                    <input
                        id="username"                     // connects label to input
                        name="username"                   // name used in POST data
                        type="text"                       // input type
                        placeholder="Enter your username" // helper text
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        // repopulates username if form fails
                    />
                </div>

                <!-- Password label -->
                <label class="label" for="password">Password</label>

                <!-- Password input container -->
                <div class="field">

                    <!-- Lock icon -->
                    <span class="icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="11" width="16" height="10" rx="2"></rect>
                            <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                        </svg>
                    </span>

                    <!-- Password input -->
                    <input
                        id="password"
                        name="password"
                        type="password"                   // hides characters
                        placeholder="Enter your password"
                    />
                </div>

                <!-- Forgot password link row -->
                <div class="row">
                    <a class="link" href="forgot_password.php">
                        Forgot password?
                    </a>
                </div>

                <!-- Submit button -->
                <button class="btn" type="submit">
                    Sign In
                </button>

            </form>
        </section>
    </main>

</body>
</html>

