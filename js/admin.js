/* ==========================================================================
   GYMZONE - Frontend Admin Dashboard Controller
   ========================================================================== */

document.addEventListener("DOMContentLoaded", () => {
  const currentUser = getCurrentUser();
  const adminProtectionWarning = document.getElementById("adminProtectionWarning");
  const adminDashboardContent = document.getElementById("adminDashboardContent");

  if (!currentUser || currentUser.role !== "admin") {
    if (adminProtectionWarning) adminProtectionWarning.style.display = "block";
    if (adminDashboardContent) adminDashboardContent.style.display = "none";
    return;
  }

  if (adminProtectionWarning) adminProtectionWarning.style.display = "none";
  if (adminDashboardContent) adminDashboardContent.style.display = "block";

  initAdminDashboard();
});

function initAdminDashboard() {
  renderAdminStats();
  renderPendingGymsTable();
  renderAllGymsTable();
  renderUsersTable();
  renderBookingsTable();
}

function renderAdminStats() {
  const allGyms = getGymsFromStorage();
  const allUsers = getUsersFromStorage();
  const allBookings = getBookingsFromStorage();

  const totalGymsEl = document.getElementById("adminTotalGyms");
  const pendingGymsEl = document.getElementById("adminPendingGyms");
  const totalUsersEl = document.getElementById("adminTotalUsers");
  const totalBookingsEl = document.getElementById("adminTotalBookings");

  if (totalGymsEl) totalGymsEl.textContent = allGyms.length;
  if (pendingGymsEl) pendingGymsEl.textContent = allGyms.filter(g => g.status === "pending").length;
  if (totalUsersEl) totalUsersEl.textContent = allUsers.length;
  if (totalBookingsEl) totalBookingsEl.textContent = allBookings.length;
}

function renderPendingGymsTable() {
  const tableBody = document.getElementById("adminPendingGymsBody");
  if (!tableBody) return;

  const pendingGyms = getGymsFromStorage().filter(g => g.status === "pending");

  if (pendingGyms.length === 0) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="5" style="text-align: center; padding: 25px; color: var(--text-muted);">
          <i class="fas fa-check-circle" style="color: var(--success-color); font-size: 1.5rem; margin-bottom: 5px;"></i>
          <p>No pending gym approval requests. All submitted listings are processed!</p>
        </td>
      </tr>
    `;
    return;
  }

  tableBody.innerHTML = pendingGyms.map(gym => `
    <tr>
      <td>
        <div style="display: flex; align-items: center; gap: 12px;">
          <img src="${gym.image}" alt="${gym.name}" style="width: 45px; height: 45px; border-radius: var(--radius-md); object-fit: cover;">
          <div>
            <strong style="color: var(--text-white);">${gym.name}</strong>
            <div style="font-size: 0.75rem; color: var(--text-muted);">Owner ID: ${gym.ownerId}</div>
          </div>
        </div>
      </td>
      <td><i class="fas fa-map-marker-alt" style="color: var(--accent-orange);"></i> ${gym.location}</td>
      <td><strong>₹${gym.price}</strong> / mo</td>
      <td><span class="badge badge-warning">Pending Review</span></td>
      <td>
        <div style="display: flex; gap: 8px;">
          <button onclick="approveGym('${gym.id}')" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Approve</button>
          <button onclick="rejectGym('${gym.id}')" class="btn btn-outline btn-sm" style="color: var(--danger-color); border-color: var(--danger-color);"><i class="fas fa-times"></i> Reject</button>
        </div>
      </td>
    </tr>
  `).join("");
}

function renderAllGymsTable() {
  const tableBody = document.getElementById("adminAllGymsBody");
  if (!tableBody) return;

  const gyms = getGymsFromStorage();

  tableBody.innerHTML = gyms.map(gym => `
    <tr>
      <td><strong>${gym.name}</strong></td>
      <td>${gym.location}</td>
      <td>₹${gym.price}</td>
      <td>
        <button onclick="toggleFeaturedGym('${gym.id}')" class="badge ${gym.featured ? 'badge-orange' : 'badge-warning'}" style="cursor: pointer;">
          ${gym.featured ? 'Featured ★' : 'Standard'}
        </button>
      </td>
      <td><span class="badge ${gym.status === 'approved' ? 'badge-success' : 'badge-warning'}">${gym.status}</span></td>
      <td>
        <button onclick="deleteAdminGym('${gym.id}')" class="btn btn-outline btn-sm" style="color: var(--danger-color); border-color: var(--danger-color);"><i class="fas fa-trash"></i> Delete</button>
      </td>
    </tr>
  `).join("");
}

function renderUsersTable() {
  const tableBody = document.getElementById("adminUsersTableBody");
  if (!tableBody) return;

  const users = getUsersFromStorage();

  tableBody.innerHTML = users.map(user => `
    <tr>
      <td>
        <div style="display: flex; align-items: center; gap: 10px;">
          <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">${user.avatar || 'U'}</div>
          <strong>${user.name}</strong>
        </div>
      </td>
      <td>${user.email}</td>
      <td><span class="badge ${user.role === 'admin' ? 'badge-danger' : user.role === 'owner' ? 'badge-orange' : 'badge-success'}">${user.role}</span></td>
      <td>${user.phone || 'N/A'}</td>
      <td>${user.joinedDate || '2026-01-01'}</td>
    </tr>
  `).join("");
}

function renderBookingsTable() {
  const tableBody = document.getElementById("adminBookingsTableBody");
  if (!tableBody) return;

  const bookings = getBookingsFromStorage();

  if (bookings.length === 0) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="6" style="text-align: center; padding: 25px; color: var(--text-muted);">No trial bookings found.</td>
      </tr>
    `;
    return;
  }

  tableBody.innerHTML = bookings.map(bk => `
    <tr>
      <td><strong style="color: var(--accent-orange);">${bk.id}</strong></td>
      <td>${bk.userName} (${bk.userPhone})</td>
      <td>${bk.gymName}</td>
      <td>${bk.date} | ${bk.timeSlot}</td>
      <td><span class="badge ${bk.status === 'Confirmed' ? 'badge-success' : 'badge-danger'}">${bk.status}</span></td>
    </tr>
  `).join("");
}

function approveGym(gymId) {
  const gym = getGymById(gymId);
  if (gym) {
    gym.status = "approved";
    updateGymListing(gym);
    showToast(`"${gym.name}" has been approved and published to Explore!`, "success");
    initAdminDashboard();
  }
}

function rejectGym(gymId) {
  if (confirm("Are you sure you want to reject and remove this listing?")) {
    deleteGymListing(gymId);
    showToast("Gym listing rejected.", "info");
    initAdminDashboard();
  }
}

function toggleFeaturedGym(gymId) {
  const gym = getGymById(gymId);
  if (gym) {
    gym.featured = !gym.featured;
    updateGymListing(gym);
    showToast(`Updated featured status for "${gym.name}".`, "info");
    initAdminDashboard();
  }
}

function deleteAdminGym(gymId) {
  if (confirm("Are you sure you want to delete this gym from system?")) {
    deleteGymListing(gymId);
    showToast("Gym listing deleted.", "info");
    initAdminDashboard();
  }
}
