// RiversideClinic/js/app.js

// -----------------------------
// API helper (fetch + JSON)
// -----------------------------
async function api(url, method = "GET", body = null) {
  const opts = { method, headers: {} };

  if (body !== null) {
    opts.headers["Content-Type"] = "application/json";
    opts.body = JSON.stringify(body);
  }

  const res = await fetch(url, opts);
  const text = await res.text();

  let data = null;
  try {
    data = text ? JSON.parse(text) : null;
  } catch (e) {
    data = { raw: text };
  }

  if (!res.ok) {
    throw (data && data.error)
      ? data.error
      : (data && data.raw ? data.raw : "Request failed");
  }

  return data;
}

// -----------------------------
// Role normalization
// -----------------------------
function normalizeRole(role) {
  role = (role || "").trim().toLowerCase();

  if (role === "administrator") return "admin";
  if (role === "admin") return "admin";
  if (role === "doctor") return "doctor";
  if (role === "nurse") return "nurse";
  if (role === "receptionist") return "receptionist";

  return role;
}

// -----------------------------
// Boot: check session
// -----------------------------
async function start() {
  try {
    const user = await api("api/auth/me.php");

    document.querySelector(".app")?.classList.remove("logged-out");

    const welcome = document.getElementById("welcome");
    if (welcome) {
      welcome.innerText = `${user.name} (${user.role})`;
    }

    const role = normalizeRole(user.role);
    renderDashboardShell(role);
  } catch (err) {
    showLogin();
  }
}

// -----------------------------
// Login UI
// -----------------------------
function showLogin() {
  document.querySelector(".app")?.classList.add("logged-out");

  const menu = document.getElementById("menu");
  if (menu) menu.innerHTML = "";

  const welcome = document.getElementById("welcome");
  if (welcome) welcome.innerText = "";

  document.getElementById("content").innerHTML = `
    <div class="login-screen">
      <div class="login-left">
        <div class="login-logo-wrap">
          <img
            src="assets/images/Desert 1 Circle.png"
            alt="Riverside Clinic Logo"
            class="login-logo"
          >
          <img
            src="assets/images/Desert Riverside Text.png"
            alt="Riverside Clinic"
            class="login-logo-text"
          >
        </div>
      </div>

      <div class="login-right">
        <div class="login-card">
          <div class="login-card-inner">
            <p class="login-eyebrow">Riverside Family Clinic</p>
            <h1>Welcome Back</h1>
            <p class="login-subtext">Sign in to access the clinic portal.</p>

            <form id="loginForm" class="login-form">
              <label for="u">Username</label>
              <input
                id="u"
                name="username"
                type="text"
                placeholder="Enter username"
                autocomplete="username"
              >

              <label for="p">Password</label>
              <input
                id="p"
                name="password"
                type="password"
                placeholder="Enter password"
                autocomplete="current-password"
              >

              <button type="submit" class="login-btn">Login</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  `;

  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      await doLogin();
    });
  }
}

// -----------------------------
// Login action
// -----------------------------
async function doLogin() {
  const usernameInput = document.getElementById("u");
  const passwordInput = document.getElementById("p");

  const username = usernameInput ? usernameInput.value.trim() : "";
  const password = passwordInput ? passwordInput.value : "";

  if (!username || !password) {
    alert("Please enter your username and password.");
    return;
  }

  try {
    await api("api/auth/login.php", "POST", { username, password });
    location.reload();
  } catch (err) {
    alert("Invalid username or password");
    console.error("Login error:", err);
  }
}

// -----------------------------
// Dashboard Shell + Menu Routing
// -----------------------------
function renderDashboardShell(role) {
  document.getElementById("content").innerHTML = `
    <div class="dashboard">
      <div class="dash-main">
        <div id="dash_view"></div>
      </div>
    </div>
  `;

  if (typeof buildMenu === "function") {
    buildMenu(role);
  }

  if (role === "admin") {
    admin_home();
    return;
  }

  if (role === "doctor") {
    doc_home();
    return;
  }

  if (role === "nurse") {
    nurse_home();
    return;
  }

  if (role === "receptionist") {
    rx_home();
    return;
  }

  setView(`
    <div style="padding:10px;">
      <b>Unknown role:</b> ${role}<br><br>
      <button class="nav-btn logout" onclick="doLogout()">Logout</button>
    </div>
  `);
}

// -----------------------------
// View helper
// -----------------------------
function setView(html) {
  const view = document.getElementById("dash_view");
  if (view) view.innerHTML = html;
}

// -----------------------------
// Logout
// -----------------------------
async function doLogout() {
  try {
    await api("api/auth/logout.php", "POST", {});
  } catch (e) {
    // ignore
  }

  location.reload();
}

// -----------------------------
// Admin Views
// -----------------------------
function admin_home() {
  if (typeof loadAdmin === "function") {
    loadAdmin();
    return;
  }

  setView(`
    <div class="page-header">
      <h2>Administrator Dashboard</h2>
      <p>Admin dashboard is loading.</p>
    </div>
  `);
}

function admin_users() {
  if (typeof admin_showUsers === "function") {
    admin_showUsers();
    return;
  }

  setView(`
    <div class="page-header">
      <h2>User Management</h2>
      <p>User management page coming soon.</p>
    </div>
  `);
}

function admin_appointments() {
  setView(`
    <div class="page-header">
      <h2>Appointments</h2>
      <p>View and manage clinic scheduling.</p>
    </div>
    <div class="admin-panel-box">
      <p>Appointments page coming soon.</p>
    </div>
  `);
}

function admin_staff() {
  setView(`
    <div class="page-header">
      <h2>Staff</h2>
      <p>Manage clinic staff and role assignments.</p>
    </div>
    <div class="admin-panel-box">
      <p>Staff management page coming soon.</p>
    </div>
  `);
}

function admin_reports() {
  if (typeof admin_showReports === "function") {
    admin_showReports();
    return;
  }

  setView(`
    <div class="page-header">
      <h2>Reports</h2>
      <p>Review clinic activity and reporting summaries.</p>
    </div>
    <div class="admin-panel-box">
      <p>Reports page coming soon.</p>
    </div>
  `);
}

function admin_settings() {
  setView(`
    <div class="page-header">
      <h2>Settings</h2>
      <p>Adjust clinic system preferences and admin settings.</p>
    </div>
    <div class="admin-panel-box">
      <p>Settings page coming soon.</p>
    </div>
  `);
}

// -----------------------------
// Doctor Views
// -----------------------------
function doc_home() {
  if (typeof loadDoctor === "function") {
    loadDoctor();
    return;
  }

  setView(`
    <h2>Doctor Dashboard</h2>
    <p>TODO: Today’s schedule + quick visit note entry.</p>
  `);
}

function doc_schedule() {
  setView(`
    <h2>My Schedule</h2>
    <p>TODO: Pull appointments for this provider.</p>
  `);
}

function doc_notes() {
  setView(`
    <h2>Visit Notes</h2>
    <p>TODO: Create/view visit notes.</p>
  `);
}

function doc_patients() {
  setView(`
    <h2>Patients</h2>
    <p>TODO: Doctor patient list view.</p>
  `);
}

// -----------------------------
// Nurse Views
// -----------------------------
function nurse_home() {
  if (typeof loadNurse === "function") {
    loadNurse();
    return;
  }

  setView(`
    <h2>Nurse Dashboard</h2>
    <p>TODO: Patient queue + intake shortcuts.</p>
  `);
}

function nurse_schedule() {
  if (typeof loadNurse === "function") {
    loadNurse();
    return;
  }

  setView(`
    <h2>Schedules</h2>
    <p>TODO: Nurse schedule view.</p>
  `);
}

function nurse_intake() {
  if (typeof loadNurse === "function") {
    loadNurse();
    return;
  }

  setView(`
    <h2>Intake</h2>
    <p>TODO: Initial visit documentation form.</p>
  `);
}

// -----------------------------
// Receptionist Views
// -----------------------------
function rx_home() {
  if (typeof loadReception === "function") {
    loadReception();
    return;
  }

  setView(`
    <h2>Receptionist Dashboard</h2>
    <p>Reception dashboard coming soon.</p>
  `);
}

function rx_registerPatient() {
  if (typeof loadReception === "function") {
    loadReception();
  }

  if (typeof rx_showPatientCreate === "function") {
    rx_showPatientCreate();
    return;
  }

  setView(`
    <h2>Register Patient</h2>
    <p>Patient registration page coming soon.</p>
  `);
}

function rx_appointments() {
  if (typeof loadReception === "function") {
    loadReception();
  }

  if (typeof rx_showAppointmentBoard === "function") {
    rx_showAppointmentBoard();
    return;
  }

  setView(`
    <h2>Appointments</h2>
    <p>Reception appointments page coming soon.</p>
  `);
}

// Start the app
start();