<?php
// ---------------------------
// Staff Dashboard (No DB Yet)
// ---------------------------

// Start session (later you'll read role + user info from $_SESSION)
session_start();

// TEMP: Set a mock user context (replace with DB/session later)
$displayName = "Staff Member";           // Mock display name
$roleName    = "Staff Member";           // Mock role label
$initials    = "SM";                     // Mock avatar initials

// TEMP: Mock data for dashboard widgets (replace with DB queries later)
$kpis = [
    ["label" => "Total Patients Today",     "value" => 24,  "sub" => "+3 from yesterday",   "icon" => "users", "tone" => "teal"],
    ["label" => "Appointments",             "value" => 18,  "sub" => "6 remaining",         "icon" => "calendar", "tone" => "teal"],
    ["label" => "Checked In",               "value" => 12,  "sub" => "5 waiting",           "icon" => "checkin", "tone" => "gold"],
    ["label" => "Intake Forms Submitted",   "value" => 156, "sub" => "+12% this month",     "icon" => "file", "tone" => "teal-soft"],
];

// Mock appointment list
$appointments = [
    ["time" => "09:00 AM", "patient" => "John Smith",    "reason" => "Annual Checkup", "provider" => "Dr. Johnson",   "status" => "Completed"],
    ["time" => "09:30 AM", "patient" => "Emily Davis",   "reason" => "Follow-up",      "provider" => "Dr. Chen",      "status" => "Completed"],
    ["time" => "10:00 AM", "patient" => "Michael Brown", "reason" => "Consultation",   "provider" => "Dr. Rodriguez", "status" => "In Progress"],
    ["time" => "10:30 AM", "patient" => "Sarah Wilson",  "reason" => "Lab Results",    "provider" => "Dr. Johnson",   "status" => "Checked In"],
    ["time" => "11:00 AM", "patient" => "David Martinez","reason" => "Physical Exam",  "provider" => "Dr. Williams",  "status" => "Scheduled"],
    ["time" => "11:30 AM", "patient" => "Lisa Anderson", "reason" => "Cardiology",     "provider" => "Dr. Chen",      "status" => "Scheduled"],
    ["time" => "01:00 PM", "patient" => "James Taylor",  "reason" => "Sick Visit",     "provider" => "Dr. Rodriguez", "status" => "Scheduled"],
];

// Mock waiting room list
$waitingRoom = [
    ["patient" => "Sarah Wilson",   "provider" => "Dr. Johnson",  "checkin" => "10:25 AM", "wait" => "5 min"],
    ["patient" => "Robert Garcia",  "provider" => "Dr. Chen",     "checkin" => "10:35 AM", "wait" => "15 min"],
    ["patient" => "Jennifer Lee",   "provider" => "Dr. Williams", "checkin" => "10:40 AM", "wait" => "20 min"],
    ["patient" => "Thomas White",   "provider" => "Dr. Rodriguez","checkin" => "10:45 AM", "wait" => "25 min"],
];

// Mock recent activity list
$recentActivity = [
    ["text" => "Dr. Johnson completed appointment with John Smith", "time" => "5 minutes ago",  "tone" => "success"],
    ["text" => "Sarah Wilson checked in for appointment",           "time" => "12 minutes ago", "tone" => "info"],
    ["text" => "New appointment scheduled for Lisa Anderson",       "time" => "25 minutes ago", "tone" => "purple"],
    ["text" => "Dr. Chen completed appointment with Emily Davis",   "time" => "38 minutes ago", "tone" => "success"],
];

// Helper: Return a badge class based on appointment status
function status_badge_class(string $status): string {
    // Normalize status for simple matching
    $s = strtolower(trim($status));

    // Map each status to a CSS class
    if ($s === "completed")   return "badge badge--success";
    if ($s === "in progress") return "badge badge--info";
    if ($s === "checked in")  return "badge badge--purple";
    return "badge badge--neutral"; // default: scheduled/unknown
}

// Helper: Return an activity dot class
function activity_dot_class(string $tone): string {
    // Normalize
    $t = strtolower(trim($tone));

    // Map
    if ($t === "success") return "dot dot--success";
    if ($t === "info")    return "dot dot--info";
    if ($t === "purple")  return "dot dot--purple";
    return "dot dot--neutral";
}

// Helper: Get today's date text (static look; replace with timezone handling later)
$todayText = date("l, F j, Y"); // Example: Monday, February 16, 2026
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Page encoding -->
    <meta charset="UTF-8">

    <!-- Responsive scaling -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Title in browser tab -->
    <title>Staff Dashboard – Riverside Family Clinic</title>

    <!-- Favicon (update filename if yours differs) -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">

    <!-- Dashboard stylesheet -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <!-- App shell: sidebar + main content -->
    <div class="app">

        <!-- Sidebar -->
        <aside class="sidebar" aria-label="Clinic Staff Navigation">
            <!-- Sidebar header -->
            <div class="sidebar__header">
                <!-- Brand label -->
                <div class="sidebar__brand">Clinic Staff</div>

                <!-- Collapse icon (UI only; can be wired to JS later) -->
                <button class="sidebar__close" type="button" aria-label="Close sidebar">
                    ×
                </button>
            </div>

            <!-- Sidebar navigation -->
            <nav class="nav">
                <!-- Active item -->
                <a class="nav__item nav__item--active" href="#">
                    <span class="nav__icon">⌁</span>
                    <span class="nav__text">Dashboard</span>
                </a>

                <!-- Links (wire later) -->
                <a class="nav__item" href="#">
                    <span class="nav__icon">📝</span>
                    <span class="nav__text">Patient Intake</span>
                </a>

                <a class="nav__item" href="#">
                    <span class="nav__icon">📁</span>
                    <span class="nav__text">Intake Records</span>
                </a>

                <a class="nav__item" href="#">
                    <span class="nav__icon">👤</span>
                    <span class="nav__text">Patients</span>
                </a>

                <a class="nav__item" href="#">
                    <span class="nav__icon">📅</span>
                    <span class="nav__text">Appointments</span>
                </a>

                <a class="nav__item" href="#">
                    <span class="nav__icon">✅</span>
                    <span class="nav__text">Check-In/Out</span>
                </a>
            </nav>

            <!-- Sidebar footer actions -->
            <div class="sidebar__footer">
                <a class="nav__item" href="#">
                    <span class="nav__icon">⚙️</span>
                    <span class="nav__text">Settings</span>
                </a>

                <!-- Placeholder logout (wire later to logout.php) -->
                <a class="nav__item nav__item--logout" href="#">
                    <span class="nav__icon">⤴</span>
                    <span class="nav__text">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main content area -->
        <main class="main">

            <!-- Top header row -->
            <header class="topbar">
                <!-- Left: page title + date -->
                <div class="topbar__left">
                    <h1 class="topbar__title">Dashboard</h1>
                    <div class="topbar__sub"><?php echo htmlspecialchars($todayText); ?></div>
                </div>

                <!-- Right: user info -->
                <div class="topbar__right">
                    <div class="user">
                        <div class="user__meta">
                            <div class="user__name"><?php echo htmlspecialchars($displayName); ?></div>
                            <div class="user__role"><?php echo htmlspecialchars($roleName); ?></div>
                        </div>

                        <div class="user__avatar" aria-label="User avatar">
                            <?php echo htmlspecialchars($initials); ?>
                        </div>
                    </div>
                </div>
            </header>

            <!-- KPI cards -->
            <section class="kpi-grid" aria-label="Key performance indicators">
                <?php foreach ($kpis as $kpi): ?>
                    <article class="kpi-card">
                        <div class="kpi-card__left">
                            <div class="kpi-card__label"><?php echo htmlspecialchars($kpi["label"]); ?></div>
                            <div class="kpi-card__value"><?php echo htmlspecialchars((string)$kpi["value"]); ?></div>
                            <div class="kpi-card__sub"><?php echo htmlspecialchars($kpi["sub"]); ?></div>
                        </div>

                        <div class="kpi-card__icon kpi-card__icon--<?php echo htmlspecialchars($kpi["tone"]); ?>">
                            <?php
                            // Render a simple icon glyph based on type (swap to SVG later if you want)
                            $icon = $kpi["icon"];
                            if ($icon === "users")    echo "👥";
                            elseif ($icon === "calendar") echo "📅";
                            elseif ($icon === "checkin")  echo "🧍";
                            else echo "📄";
                            ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>

            <!-- Middle grid: appointments + waiting room -->
            <section class="mid-grid" aria-label="Appointments and waiting room">

                <!-- Appointments panel -->
                <section class="panel">
                    <div class="panel__header">
                        <h2 class="panel__title">Today's Appointments</h2>
                    </div>

                    <div class="panel__body">
                        <?php foreach ($appointments as $a): ?>
                            <div class="appt">
                                <div class="appt__time"><?php echo htmlspecialchars($a["time"]); ?></div>

                                <div class="appt__info">
                                    <div class="appt__patient"><?php echo htmlspecialchars($a["patient"]); ?></div>
                                    <div class="appt__reason"><?php echo htmlspecialchars($a["reason"]); ?></div>
                                </div>

                                <div class="appt__provider"><?php echo htmlspecialchars($a["provider"]); ?></div>

                                <div class="appt__status">
                                    <span class="<?php echo htmlspecialchars(status_badge_class($a["status"])); ?>">
                                        <?php echo htmlspecialchars($a["status"]); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Waiting room panel -->
                <section class="panel">
                    <div class="panel__header panel__header--warning">
                        <h2 class="panel__title">Waiting Room</h2>
                    </div>

                    <div class="panel__body">
                        <?php foreach ($waitingRoom as $w): ?>
                            <div class="wait">
                                <div class="wait__left">
                                    <div class="wait__patient"><?php echo htmlspecialchars($w["patient"]); ?></div>
                                    <div class="wait__provider"><?php echo htmlspecialchars($w["provider"]); ?></div>
                                    <div class="wait__checkin">Checked in: <?php echo htmlspecialchars($w["checkin"]); ?></div>
                                </div>

                                <div class="wait__pill">
                                    <?php echo htmlspecialchars($w["wait"]); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </section>

            <!-- Quick action cards -->
            <section class="actions" aria-label="Quick actions">
                <a class="action-card action-card--tealsoft" href="#">
                    <div class="action-card__icon">📅</div>
                    <div class="action-card__title">Schedule Appointment</div>
                    <div class="action-card__sub">Book a new appointment for a patient</div>
                </a>

                <a class="action-card action-card--teal" href="#">
                    <div class="action-card__icon">🧍</div>
                    <div class="action-card__title">Check-In Patient</div>
                    <div class="action-card__sub">Mark a patient as arrived</div>
                </a>

                <a class="action-card action-card--gold" href="#">
                    <div class="action-card__icon">👥</div>
                    <div class="action-card__title">Add New Patient</div>
                    <div class="action-card__sub">Register a new patient in the system</div>
                </a>
            </section>

            <!-- Recent activity -->
            <section class="panel panel--full" aria-label="Recent activity">
                <div class="panel__header">
                    <h2 class="panel__title">Recent Activity</h2>
                </div>

                <div class="panel__body">
                    <?php foreach ($recentActivity as $r): ?>
                        <div class="activity">
                            <div class="<?php echo htmlspecialchars(activity_dot_class($r["tone"])); ?>"></div>

                            <div class="activity__text">
                                <div class="activity__main"><?php echo htmlspecialchars($r["text"]); ?></div>
                                <div class="activity__time"><?php echo htmlspecialchars($r["time"]); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </main>
    </div>
</body>
</html>
