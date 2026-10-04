/* ==========================================================================
   GYMZONE - Demo Authentication & User Management (localStorage)
   ========================================================================== */

const INITIAL_USERS = [
  {
    id: "user_member",
    name: "Rahul Kumar",
    email: "user@gymzone.com",
    password: "pass123",
    role: "member",
    phone: "+91 98765 43210",
    avatar: "RK",
    joinedDate: "2026-01-15"
  },
  {
    id: "user_owner",
    name: "Vikram Sen",
    email: "owner@gymzone.com",
    password: "pass123",
    role: "owner",
    phone: "+91 98351 12345",
    avatar: "VS",
    joinedDate: "2026-02-10"
  },
  {
    id: "user_admin",
    name: "System Admin",
    email: "admin@gymzone.com",
    password: "pass123",
    role: "admin",
    phone: "+91 99999 00000",
    avatar: "SA",
    joinedDate: "2026-01-01"
  }
];

function getUsersFromStorage() {
  const data = localStorage.getItem("gymzone_users");
  if (!data) {
    localStorage.setItem("gymzone_users", JSON.stringify(INITIAL_USERS));
    return INITIAL_USERS;
  }
  try {
    return JSON.parse(data);
  } catch (e) {
    localStorage.setItem("gymzone_users", JSON.stringify(INITIAL_USERS));
    return INITIAL_USERS;
  }
}

function saveUsersToStorage(users) {
  localStorage.setItem("gymzone_users", JSON.stringify(users));
}

function getCurrentUser() {
  const data = localStorage.getItem("gymzone_current_user");
  if (!data) return null;
  try {
    return JSON.parse(data);
  } catch (e) {
    return null;
  }
}

function setCurrentUser(user) {
  if (user) {
    localStorage.setItem("gymzone_current_user", JSON.stringify(user));
  } else {
    localStorage.removeItem("gymzone_current_user");
  }
}

function registerUser(name, email, password, role = "member", phone = "") {
  const users = getUsersFromStorage();
  const existing = users.find(u => u.email.toLowerCase() === email.toLowerCase());
  if (existing) {
    return { success: false, message: "An account with this email already exists." };
  }

  const initials = name.split(" ").map(n => n[0]).join("").toUpperCase().substring(0, 2) || "U";
  const newUser = {
    id: "user_" + Date.now(),
    name,
    email,
    password,
    role,
    phone,
    avatar: initials,
    joinedDate: new Date().toISOString().split("T")[0]
  };

  users.push(newUser);
  saveUsersToStorage(users);
  setCurrentUser(newUser);

  return { success: true, user: newUser };
}

function loginUser(email, password) {
  const users = getUsersFromStorage();
  const user = users.find(u => u.email.toLowerCase() === email.toLowerCase() && u.password === password);
  if (!user) {
    return { success: false, message: "Invalid email or password." };
  }

  setCurrentUser(user);
  return { success: true, user };
}

function logoutUser() {
  setCurrentUser(null);
  window.location.href = "index.html";
}

function updateUserProfile(updatedUser) {
  let users = getUsersFromStorage();
  const index = users.findIndex(u => u.id === updatedUser.id);
  if (index !== -1) {
    users[index] = { ...users[index], ...updatedUser };
    saveUsersToStorage(users);
    setCurrentUser(users[index]);
    return true;
  }
  return false;
}

// Auto seed users
getUsersFromStorage();
