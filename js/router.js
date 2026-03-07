function buildMenu(role){
  const menu = document.getElementById("menu");
  menu.innerHTML = "";

  if(role === "Administrator"){
    menu.innerHTML = `
      <button onclick="admin_home()">Admin Home</button>
      <button onclick="admin_users()">Manage Users</button>
      <button onclick="admin_reports()">Reporting</button>
      <button onclick="logout()">Logout</button>
    `;
    return;
  }

  if(role === "Doctor"){
    menu.innerHTML = `
      <button onclick="loadDoctor()">My Schedule</button>
      <button onclick="logout()">Logout</button>
    `;
    return;
  }

  if(role === "Nurse"){
    menu.innerHTML = `
      <button onclick="loadNurse()">Nurse Station</button>
      <button onclick="logout()">Logout</button>
    `;
    return;
  }

  if(role === "Receptionist"){
    menu.innerHTML = `
      <button onclick="loadReception()">Front Desk</button>
      <button onclick="logout()">Logout</button>
    `;
    return;
  }
}