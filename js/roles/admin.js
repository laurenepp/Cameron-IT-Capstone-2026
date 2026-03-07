// js/roles/admin.js

function loadAdmin(){
  const content = document.getElementById("content");

  content.innerHTML = `
    <div class="admin-page-shell">
      <div class="admin-page-header">
        <h2>Administrator - System Oversight</h2>
        <p>Users, system status, reporting</p>
      </div>

      <div id="admin_tiles" class="admin-tiles-wrap"></div>
      <div id="admin_panel" class="admin-panel-wrap"></div>
    </div>
  `;

  admin_loadTiles();
  admin_showUsers();
}

function admin_loadTiles(){
  const wrap = document.getElementById("admin_tiles");
  if (!wrap) return;

  // UI-only for now
  wrap.innerHTML = "";
}

function admin_tile(label, value, sub, iconText, tone){
  return `
    <div class="tile ${tone}">
      <div class="icon">${iconText}</div>
      <div class="content">
        <div class="label">${label}</div>
        <div class="value">${value}</div>
        <div class="sub">${sub}</div>
      </div>
    </div>
  `;
}

function admin_panel(html){
  document.getElementById("admin_panel").innerHTML = html;
}

/* -------------------------
   USERS
------------------------- */

function admin_showUsers(){
  admin_panel(`
    <div class="admin-users-shell">
      <div class="admin-users-toolbar card">
        <div class="admin-search-wrap">
          <span class="admin-search-icon">&#128269;</span>
          <input
            id="adminUserSearch"
            class="admin-search-input"
            type="text"
            placeholder="Search users by name, username, email, or role..."
            oninput="admin_filterUsers()"
          >
        </div>

        <button type="button" id="adminAddUserBtn" class="admin-add-user-btn">
          <span class="plus">+</span>
          <span>Add User</span>
        </button>

        <div class="admin-users-stats" id="adminUsersStats">
          Total Users: 0 | Active: 0 | Locked: 0
        </div>
      </div>

      <div id="admin_create_wrap" style="display:none;"></div>

      <div class="card admin-users-table-card">
        <div id="admin_users"></div>
      </div>
    </div>
  `);

  const addBtn = document.getElementById("adminAddUserBtn");
  if (addBtn) {
    addBtn.addEventListener("click", function(){
      admin_showCreateUser();
    });
  }

  admin_loadUsers();
}

function admin_loadUsers(){
  // UI-only mock data for now
  window.adminUsersData = [
    {
      User_ID: 1,
      First_Name: "Reyna",
      Last_Name: "Administrator",
      Email: "reyna@clinic.com",
      Role_Name: "Administrator",
      Is_Disabled: 0,
      Last_Login_At: "-"
    },
    {
      User_ID: 2,
      First_Name: "Fernando",
      Last_Name: "Doctor",
      Email: "fernando@clinic.com",
      Role_Name: "Doctor",
      Is_Disabled: 0,
      Last_Login_At: "-"
    },
    {
      User_ID: 3,
      First_Name: "Logan",
      Last_Name: "Nurse",
      Email: "logan@clinic.com",
      Role_Name: "Nurse",
      Is_Disabled: 0,
      Last_Login_At: "-"
    },
    {
      User_ID: 4,
      First_Name: "Michael",
      Last_Name: "Phillips",
      Email: "michael@clinic.com",
      Role_Name: "Administrator",
      Is_Disabled: 0,
      Last_Login_At: "-"
    },
    {
      User_ID: 5,
      First_Name: "Andrea",
      Last_Name: "Receptionist",
      Email: "andrea@clinic.com",
      Role_Name: "Receptionist",
      Is_Disabled: 0,
      Last_Login_At: "-"
    }
  ];

  const totalUsers = window.adminUsersData.length;
  const activeUsers = window.adminUsersData.filter(u => !Number(u.Is_Disabled)).length;
  const lockedUsers = window.adminUsersData.filter(u => Number(u.Is_Disabled)).length;

  const stats = document.getElementById("adminUsersStats");
  if (stats) {
    stats.innerHTML = `Total Users: ${totalUsers} | Active: ${activeUsers} | Locked: ${lockedUsers}`;
  }

  admin_renderUsersTable(window.adminUsersData);
}

function admin_filterUsers(){
  const searchEl = document.getElementById("adminUserSearch");
  const term = (searchEl?.value || "").trim().toLowerCase();

  if (!window.adminUsersData) return;

  if (!term) {
    admin_renderUsersTable(window.adminUsersData);
    return;
  }

  const filtered = window.adminUsersData.filter(u => {
    const fullName = `${u.First_Name || ""} ${u.Last_Name || ""}`.toLowerCase();
    const reverseName = `${u.Last_Name || ""}, ${u.First_Name || ""}`.toLowerCase();
    const email = (u.Email || "").toLowerCase();
    const role = (u.Role_Name || "").toLowerCase();
    const id = String(u.User_ID || "").toLowerCase();

    return (
      fullName.includes(term) ||
      reverseName.includes(term) ||
      email.includes(term) ||
      role.includes(term) ||
      id.includes(term)
    );
  });

  admin_renderUsersTable(filtered);
}

function admin_renderUsersTable(users){
  const rows = users.map(u => `
    <tr>
      <td>
        <div class="admin-user-cell">
          <div class="admin-user-avatar">
            ${((u.First_Name || "").charAt(0) + (u.Last_Name || "").charAt(0)).toUpperCase() || "U"}
          </div>
          <div class="admin-user-meta">
            <div class="admin-user-name">${u.First_Name || ""} ${u.Last_Name || ""}</div>
            <div class="admin-user-sub">${u.Email || ""}</div>
          </div>
        </div>
      </td>
      <td>
        <span class="admin-role-pill role-${(u.Role_Name || "").toLowerCase()}">
          ${(u.Role_Name || "").toLowerCase()}
        </span>
      </td>
      <td>
        ${
          Number(u.Is_Disabled)
            ? `<span class="admin-status-pill locked">locked</span>`
            : `<span class="admin-status-pill active">active</span>`
        }
      </td>
      <td>${u.Last_Login_At || "-"}</td>
      <td class="admin-actions-cell">
        <button class="small ghost" onclick='admin_editUser(${JSON.stringify(u)})'>Edit</button>
        <button
          class="small ${u.Is_Disabled ? "secondary" : "gold"}"
          onclick="admin_toggleDisable(${u.User_ID}, ${u.Is_Disabled ? 0 : 1})">
          ${u.Is_Disabled ? "Enable" : "Disable"}
        </button>
      </td>
    </tr>
  `).join("");

  document.getElementById("admin_users").innerHTML = `
    <table class="admin-users-table">
      <thead>
        <tr>
          <th>USER</th>
          <th>ROLE</th>
          <th>STATUS</th>
          <th>LAST LOGIN</th>
          <th>ACTIONS</th>
        </tr>
      </thead>
      <tbody>
        ${rows || `<tr><td colspan="5">No users found.</td></tr>`}
      </tbody>
    </table>
  `;
}

function admin_showCreateUser(){
  const wrap = document.getElementById("admin_create_wrap");
  if (!wrap) return;

  wrap.style.display = "block";
  wrap.innerHTML = `
    <div class="card admin-create-user-card">
      <h3>Add New User</h3>
      <p style="margin-top:8px;">Button click is working.</p>

      <div class="form-grid admin-create-grid">
        <div class="field">
          <label>Full Name</label>
          <input id="cu_fullname" placeholder="Enter full name">
        </div>

        <div class="field">
          <label>Username</label>
          <input id="cu_username" placeholder="Enter username">
        </div>

        <div class="field">
          <label>Email</label>
          <input id="cu_email" placeholder="user@riverside.clinic">
        </div>

        <div class="field">
          <label>Role</label>
          <select id="cu_role">
            <option>Administrator</option>
            <option>Doctor</option>
            <option>Nurse</option>
            <option>Receptionist</option>
          </select>
        </div>

        <div class="field admin-create-span-2">
          <label>Initial Password</label>
          <input id="cu_password" type="password" placeholder="Enter initial password">
        </div>
      </div>

      <div class="row" style="margin-top:16px;">
        <button class="admin-create-submit" type="button" onclick="admin_mockCreateUser()">Create User</button>
        <button class="admin-create-cancel" type="button" onclick="admin_cancelCreateUser()">Cancel</button>
      </div>

      <div id="admin_msg" style="margin-top:10px;"></div>
    </div>
  `;
}

function admin_cancelCreateUser(){
  const wrap = document.getElementById("admin_create_wrap");
  if (!wrap) return;

  wrap.style.display = "none";
  wrap.innerHTML = "";
}

function admin_mockCreateUser(){
  const msg = document.getElementById("admin_msg");
  if (!msg) return;

  msg.innerHTML = `<span class="badge gold">Form is working. Database insert is disabled until schema fixes are complete.</span>`;
}

async function admin_createUser(){
  // intentionally disabled until backend/database is ready
}

function admin_editUser(u){
  admin_panel(`
    <div class="section-title">
      <h3>Edit User #${u.User_ID}</h3>
      <div class="tools">
        <button class="ghost" onclick="admin_showUsers()">Back</button>
      </div>
    </div>

    <div class="form-grid">
      <div class="field">
        <label>First Name</label>
        <input id="eu_first" value="${u.First_Name || ""}">
      </div>
      <div class="field">
        <label>Last Name</label>
        <input id="eu_last" value="${u.Last_Name || ""}">
      </div>
      <div class="field">
        <label>Email</label>
        <input id="eu_email" value="${u.Email || ""}">
      </div>
      <div class="field">
        <label>Phone</label>
        <input id="eu_phone" value="">
      </div>
      <div class="field">
        <label>Role</label>
        <select id="eu_role">
          ${["Administrator","Doctor","Nurse","Receptionist"].map(r =>
            `<option ${r === u.Role_Name ? "selected" : ""}>${r}</option>`
          ).join("")}
        </select>
      </div>
    </div>

    <div class="row" style="margin-top:12px;">
      <button class="primary" type="button" onclick="toast('Mock Save', 'Edit form is UI-only right now.', 'ok')">Save</button>
      <button class="${u.Is_Disabled ? "secondary" : "gold"}" type="button"
        onclick="admin_toggleDisable(${u.User_ID}, ${u.Is_Disabled ? 0 : 1})">
        ${u.Is_Disabled ? "Enable" : "Disable"}
      </button>
    </div>

    <div id="admin_msg" style="margin-top:10px;"></div>
  `);
}

async function admin_updateUser(userId){
  // intentionally disabled for now
}

function admin_toggleDisable(userId, isDisabled){
  const user = window.adminUsersData.find(u => Number(u.User_ID) === Number(userId));
  if (!user) return;

  user.Is_Disabled = isDisabled ? 1 : 0;
  admin_loadUsers();
  toast("Mock Update", isDisabled ? "User disabled (UI only)" : "User enabled (UI only)", "ok");
}

/* -------------------------
   REPORTS
------------------------- */

function admin_showReports(){
  admin_panel(`
    <div class="section-title">
      <h3>Reporting - Appointments</h3>
      <div class="tools">
        <button class="ghost" onclick="admin_showUsers()">Back</button>
      </div>
    </div>

    <div class="card">
      <p>Reporting is temporarily UI-only while backend/database work is in progress.</p>
    </div>
  `);
}

async function admin_loadReport(){
  // intentionally disabled for now
}