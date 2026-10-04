/* ==========================================================================
   GYMZONE - Main Application Script (UI & Global Handlers)
   ========================================================================== */

document.addEventListener("DOMContentLoaded", () => {
  initNavbar();
  initToastContainer();
  initFavoriteHandlers();
  setActiveNavLink();
});

/* Navbar & User Menu Rendering */
function initNavbar() {
  const user = getCurrentUser();
  const navRight = document.querySelector(".nav-right");
  const hamburgerBtn = document.querySelector(".hamburger-btn");
  const navLinks = document.querySelector(".nav-links");

  if (hamburgerBtn && navLinks) {
    hamburgerBtn.addEventListener("click", () => {
      navLinks.classList.toggle("active");
    });
  }

  if (navRight) {
    if (user) {
      let roleBadge = "";
      if (user.role === "admin") {
        roleBadge = `<span class="badge badge-danger">Admin</span>`;
      } else if (user.role === "owner") {
        roleBadge = `<span class="badge badge-orange">Owner</span>`;
      }

      navRight.innerHTML = `
        <div class="user-menu">
          <button class="user-btn">
            <div class="user-avatar">${user.avatar || 'U'}</div>
            <span style="font-weight: 600; font-size: 0.9rem;">${user.name.split(' ')[0]}</span>
            <i class="fas fa-chevron-down" style="font-size: 0.75rem; color: var(--text-muted);"></i>
          </button>
          <div class="dropdown-menu">
            <div class="dropdown-header">
              <div class="dropdown-name">${user.name}</div>
              <div class="dropdown-role">${roleBadge || user.role}</div>
            </div>
            <a href="profile.html" class="dropdown-item"><i class="fas fa-user-circle"></i> My Profile</a>
            <a href="favorites.html" class="dropdown-item"><i class="fas fa-heart"></i> Saved Gyms</a>
            <a href="booking.html" class="dropdown-item"><i class="fas fa-calendar-check"></i> My Bookings</a>
            ${user.role === 'owner' ? '<a href="owner-dashboard.html" class="dropdown-item"><i class="fas fa-store"></i> Owner Dashboard</a>' : ''}
            ${user.role === 'admin' ? '<a href="admin-dashboard.html" class="dropdown-item"><i class="fas fa-user-shield"></i> Admin Dashboard</a>' : ''}
            <div style="border-top: 1px solid var(--border-color); margin: 5px 0;"></div>
            <a href="#" onclick="logoutUser(); return false;" class="dropdown-item" style="color: var(--danger-color);"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </div>
        </div>
      `;
    } else {
      navRight.innerHTML = `
        <a href="login.html" class="btn btn-outline btn-sm"><i class="fas fa-sign-in-alt"></i> Login</a>
        <a href="register.html" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Join Now</a>
      `;
    }
  }
}

function setActiveNavLink() {
  const currentPath = window.location.pathname.split("/").pop() || "index.html";
  const links = document.querySelectorAll(".nav-link");
  links.forEach(link => {
    const href = link.getAttribute("href");
    if (href === currentPath) {
      link.classList.add("active");
    } else {
      link.classList.remove("active");
    }
  });
}

/* Toast Notifications */
function initToastContainer() {
  if (!document.querySelector(".toast-container")) {
    const container = document.createElement("div");
    container.className = "toast-container";
    document.body.appendChild(container);
  }
}

function showToast(message, type = "info") {
  initToastContainer();
  const container = document.querySelector(".toast-container");
  const toast = document.createElement("div");
  toast.className = `toast toast-${type}`;
  
  let icon = "fa-info-circle";
  if (type === "success") icon = "fa-check-circle";
  if (type === "error") icon = "fa-exclamation-triangle";

  toast.innerHTML = `
    <i class="fas ${icon}" style="font-size: 1.2rem; color: ${type === 'success' ? 'var(--success-color)' : type === 'error' ? 'var(--danger-color)' : 'var(--accent-orange)'};"></i>
    <span>${message}</span>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateX(100%)";
    toast.style.transition = "all 0.3s ease";
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

/* Favorites / Wishlist Management */
function getFavorites() {
  const data = localStorage.getItem("gymzone_favorites");
  return data ? JSON.parse(data) : [];
}

function toggleFavorite(gymId) {
  let favorites = getFavorites();
  const index = favorites.indexOf(gymId);
  if (index === -1) {
    favorites.push(gymId);
    showToast("Gym added to your favorites!", "success");
  } else {
    favorites.splice(index, 1);
    showToast("Gym removed from favorites.", "info");
  }
  localStorage.setItem("gymzone_favorites", JSON.stringify(favorites));
  updateFavoriteIcons();
}

function updateFavoriteIcons() {
  const favorites = getFavorites();
  const favBtns = document.querySelectorAll(`.favorite-btn`);
  favBtns.forEach(btn => {
    const gymId = btn.getAttribute("data-id");
    if (favorites.includes(gymId)) {
      btn.classList.add("active");
      btn.innerHTML = `<i class="fas fa-heart" style="color: var(--accent-orange);"></i>`;
    } else {
      btn.classList.remove("active");
      btn.innerHTML = `<i class="far fa-heart"></i>`;
    }
  });
}

function initFavoriteHandlers() {
  updateFavoriteIcons();
}

/* Card Generator Helper */
function createGymCardHTML(gym) {
  const favorites = getFavorites();
  const isFav = favorites.includes(gym.id);
  
  let badgeHTML = '';
  if (gym.isNew) {
    badgeHTML = `<span class="badge badge-success gym-tag">NEW OPENING</span>`;
  } else if (gym.featured) {
    badgeHTML = `<span class="badge badge-orange gym-tag">FEATURED</span>`;
  }

  const facilityTags = gym.facilities.slice(0, 3).map(f => `<span class="facility-tag">${f}</span>`).join('');

  return `
    <div class="gym-card">
      <div class="gym-card-img-wrap">
        <img src="${gym.image}" alt="${gym.name}" class="gym-card-img" onerror="this.src='https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800&auto=format&fit=crop'">
        ${badgeHTML}
        <button class="favorite-btn ${isFav ? 'active' : ''}" data-id="${gym.id}" onclick="event.preventDefault(); toggleFavorite('${gym.id}');">
          <i class="${isFav ? 'fas' : 'far'} fa-heart" ${isFav ? 'style="color: var(--accent-orange);"' : ''}></i>
        </button>
        <div class="gym-rating-badge">
          <i class="fas fa-star"></i> ${gym.rating}
        </div>
      </div>
      <div class="gym-card-body">
        <div class="gym-location"><i class="fas fa-map-marker-alt"></i> ${gym.location}, Jamshedpur</div>
        <h3 class="gym-name">${gym.name}</h3>
        <div class="gym-timings"><i class="far fa-clock"></i> ${gym.timings}</div>
        <div class="gym-facilities-tags">
          ${facilityTags}
          ${gym.facilities.length > 3 ? `<span class="facility-tag">+${gym.facilities.length - 3} more</span>` : ''}
        </div>
        <div class="gym-card-footer">
          <div class="gym-price-wrap">
            <span class="price-label">Starting From</span>
            <span class="price-amount">₹${gym.price.toLocaleString()}<span>/mo</span></span>
          </div>
          <a href="gym-details.html?id=${gym.id}" class="btn btn-outline btn-sm">View Details <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
    </div>
  `;
}
