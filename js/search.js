/* ==========================================================================
   GYMZONE - Search & Filter Controller
   ========================================================================== */

document.addEventListener("DOMContentLoaded", () => {
  if (document.getElementById("exploreGymsGrid")) {
    initExplorePage();
  }

  // Hero Search Form Handler (index.html)
  const heroSearchBtn = document.getElementById("heroSearchBtn");
  if (heroSearchBtn) {
    heroSearchBtn.addEventListener("click", () => {
      const keyword = document.getElementById("heroSearchKeyword")?.value.trim() || "";
      const location = document.getElementById("heroSearchLocation")?.value || "";
      window.location.href = `explore.html?q=${encodeURIComponent(keyword)}&loc=${encodeURIComponent(location)}`;
    });
  }
});

function initExplorePage() {
  const urlParams = new URLSearchParams(window.location.search);
  const qParam = urlParams.get("q") || "";
  const locParam = urlParams.get("loc") || "";
  const newParam = urlParams.get("filter") === "new";

  const searchInput = document.getElementById("searchInput");
  const locationFilter = document.getElementById("locationFilter");
  const sortSelect = document.getElementById("sortSelect");
  const priceRange = document.getElementById("priceRange");
  const priceValueLabel = document.getElementById("priceValueLabel");
  const resetFiltersBtn = document.getElementById("resetFiltersBtn");
  const toggleFiltersBtn = document.getElementById("toggleFiltersBtn");

  if (searchInput && qParam) searchInput.value = qParam;
  if (locationFilter && locParam) locationFilter.value = locParam;

  if (priceRange && priceValueLabel) {
    priceValueLabel.textContent = `₹${priceRange.value}`;
    priceRange.addEventListener("input", (e) => {
      priceValueLabel.textContent = `₹${e.target.value}`;
      renderFilteredGyms();
    });
  }

  if (searchInput) searchInput.addEventListener("input", renderFilteredGyms);
  if (locationFilter) locationFilter.addEventListener("change", renderFilteredGyms);
  if (sortSelect) sortSelect.addEventListener("change", renderFilteredGyms);

  // Facility checkboxes listener
  const facilityCheckboxes = document.querySelectorAll(".facility-checkbox");
  facilityCheckboxes.forEach(cb => cb.addEventListener("change", renderFilteredGyms));

  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener("click", () => {
      if (searchInput) searchInput.value = "";
      if (locationFilter) locationFilter.value = "";
      if (sortSelect) sortSelect.value = "featured";
      if (priceRange) {
        priceRange.value = 5000;
        priceValueLabel.textContent = "₹5000";
      }
      facilityCheckboxes.forEach(cb => cb.checked = false);
      renderFilteredGyms();
    });
  }

  if (toggleFiltersBtn) {
    toggleFiltersBtn.addEventListener("click", () => {
      const sidebar = document.querySelector(".filters-sidebar");
      if (sidebar) sidebar.classList.toggle("active");
    });
  }

  if (newParam && sortSelect) {
    sortSelect.value = "newest";
  }

  renderFilteredGyms();
}

function renderFilteredGyms() {
  const grid = document.getElementById("exploreGymsGrid");
  const countLabel = document.getElementById("gymsCountLabel");
  if (!grid) return;

  const allGyms = getGymsFromStorage().filter(g => g.status === "approved");

  const keyword = document.getElementById("searchInput")?.value.toLowerCase().trim() || "";
  const selectedLocation = document.getElementById("locationFilter")?.value || "";
  const maxPrice = parseInt(document.getElementById("priceRange")?.value || 5000, 10);
  const sortBy = document.getElementById("sortSelect")?.value || "featured";

  // Selected facilities
  const selectedFacilities = Array.from(document.querySelectorAll(".facility-checkbox:checked")).map(cb => cb.value);

  let filtered = allGyms.filter(gym => {
    // Keyword matching
    const matchesKeyword = !keyword || 
      gym.name.toLowerCase().includes(keyword) || 
      gym.location.toLowerCase().includes(keyword) ||
      gym.address.toLowerCase().includes(keyword) ||
      gym.description.toLowerCase().includes(keyword);

    // Location matching
    const matchesLocation = !selectedLocation || gym.location.toLowerCase() === selectedLocation.toLowerCase();

    // Price matching
    const matchesPrice = gym.price <= maxPrice;

    // Facilities matching (must have all selected facilities)
    const matchesFacilities = selectedFacilities.length === 0 || 
      selectedFacilities.every(fac => gym.facilities.includes(fac));

    return matchesKeyword && matchesLocation && matchesPrice && matchesFacilities;
  });

  // Sorting logic
  if (sortBy === "price-low") {
    filtered.sort((a, b) => a.price - b.price);
  } else if (sortBy === "price-high") {
    filtered.sort((a, b) => b.price - a.price);
  } else if (sortBy === "newest") {
    filtered.sort((a, b) => (b.isNew ? 1 : 0) - (a.isNew ? 1 : 0));
  } else if (sortBy === "rating") {
    filtered.sort((a, b) => b.rating - a.rating);
  } else {
    // Featured first
    filtered.sort((a, b) => (b.featured ? 1 : 0) - (a.featured ? 1 : 0));
  }

  if (countLabel) {
    countLabel.textContent = `Showing ${filtered.length} Gym${filtered.length === 1 ? '' : 's'} in Jamshedpur`;
  }

  if (filtered.length === 0) {
    grid.innerHTML = `
      <div class="empty-state">
        <i class="fas fa-search-minus empty-icon"></i>
        <h3 class="empty-title">No Gyms Found</h3>
        <p class="empty-desc">We couldn't find any gyms matching your selected filters in Jamshedpur. Try resetting your search or adjusting price/location filters.</p>
        <button onclick="document.getElementById('resetFiltersBtn')?.click()" class="btn btn-primary"><i class="fas fa-redo"></i> Reset Filters</button>
      </div>
    `;
    return;
  }

  grid.innerHTML = filtered.map(gym => createGymCardHTML(gym)).join("");
  updateFavoriteIcons();
}
