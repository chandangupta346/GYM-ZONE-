/* ==========================================================================
   GYMZONE - Gym Owner Dashboard Controller
   ========================================================================== */

let ownerImageBase64 = "";

document.addEventListener("DOMContentLoaded", () => {
  const currentUser = getCurrentUser();
  const ownerProtectionWarning = document.getElementById("ownerProtectionWarning");
  const ownerDashboardContent = document.getElementById("ownerDashboardContent");

  if (!currentUser || (currentUser.role !== "owner" && currentUser.role !== "admin")) {
    if (ownerProtectionWarning) ownerProtectionWarning.style.display = "block";
    if (ownerDashboardContent) ownerDashboardContent.style.display = "none";
    return;
  }

  if (ownerProtectionWarning) ownerProtectionWarning.style.display = "none";
  if (ownerDashboardContent) ownerDashboardContent.style.display = "block";

  initOwnerDashboard();
});

function initOwnerDashboard() {
  renderOwnerStats();
  renderOwnerGyms();

  // Photo Upload FileReader Preview
  const imageFileInput = document.getElementById("gymImageFile");
  const imagePreview = document.getElementById("gymImagePreview");

  if (imageFileInput) {
    imageFileInput.addEventListener("change", (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (event) => {
          ownerImageBase64 = event.target.result;
          if (imagePreview) {
            imagePreview.src = ownerImageBase64;
            imagePreview.style.display = "block";
          }
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Add / Edit Gym Form Handler
  const gymForm = document.getElementById("ownerGymForm");
  if (gymForm) {
    gymForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const gymId = document.getElementById("ownerGymId").value;
      const name = document.getElementById("ownerGymName").value.trim();
      const location = document.getElementById("ownerGymLocation").value;
      const address = document.getElementById("ownerGymAddress").value.trim();
      const monthlyPrice = parseInt(document.getElementById("ownerMonthlyPrice").value, 10);
      const quarterlyPrice = parseInt(document.getElementById("ownerQuarterlyPrice").value, 10) || monthlyPrice * 3 * 0.9;
      const halfYearlyPrice = parseInt(document.getElementById("ownerHalfYearlyPrice").value, 10) || monthlyPrice * 6 * 0.85;
      const yearlyPrice = parseInt(document.getElementById("ownerYearlyPrice").value, 10) || monthlyPrice * 12 * 0.75;
      const timings = document.getElementById("ownerGymTimings").value.trim();
      const openingDays = document.getElementById("ownerGymOpeningDays").value.trim() || "Mon - Sat";
      const description = document.getElementById("ownerGymDesc").value.trim();
      const phone = document.getElementById("ownerGymPhone").value.trim();
      const email = document.getElementById("ownerGymEmail").value.trim();
      const imageUrlInput = document.getElementById("ownerGymImageUrl")?.value.trim();

      const finalImage = ownerImageBase64 || imageUrlInput || "https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800&auto=format&fit=crop";

      // Selected facilities
      const facilities = Array.from(document.querySelectorAll(".owner-facility-checkbox:checked")).map(cb => cb.value);

      const currentUser = getCurrentUser();

      if (gymId) {
        // Editing existing gym
        const existingGym = getGymById(gymId);
        const updatedGym = {
          ...existingGym,
          name,
          location,
          address,
          price: monthlyPrice,
          plans: {
            monthly: monthlyPrice,
            quarterly: Math.round(quarterlyPrice),
            halfYearly: Math.round(halfYearlyPrice),
            yearly: Math.round(yearlyPrice)
          },
          timings,
          openingDays,
          description,
          phone,
          email,
          facilities: facilities.length > 0 ? facilities : existingGym.facilities,
          image: finalImage
        };

        if (updateGymListing(updatedGym)) {
          showToast("Gym listing updated successfully!", "success");
        }
      } else {
        // Adding new gym listing
        const newGym = {
          id: "gym_" + Date.now(),
          name,
          location,
          city: "Jamshedpur",
          address,
          price: monthlyPrice,
          plans: {
            monthly: monthlyPrice,
            quarterly: Math.round(quarterlyPrice),
            halfYearly: Math.round(halfYearlyPrice),
            yearly: Math.round(yearlyPrice)
          },
          rating: 5.0,
          reviewCount: 0,
          image: finalImage,
          gallery: [finalImage],
          facilities: facilities.length > 0 ? facilities : ["AC", "Cardio", "Locker Room"],
          timings,
          openingDays,
          phone,
          email,
          status: currentUser.role === "admin" ? "approved" : "pending",
          featured: false,
          isNew: true,
          ownerId: currentUser.id,
          description,
          trainers: [
            { name: "Certified Trainer", role: "Fitness Lead", img: "https://images.unsplash.com/photo-1567013127542-490d757e51fc?q=80&w=300&auto=format&fit=crop" }
          ],
          reviews: []
        };

        addGymListing(newGym);
        showToast(currentUser.role === "admin" ? "Gym listing created and published!" : "Gym submitted for admin approval!", "success");
      }

      closeOwnerModal();
      renderOwnerStats();
      renderOwnerGyms();
    });
  }
}

function renderOwnerStats() {
  const currentUser = getCurrentUser();
  if (!currentUser) return;

  const allGyms = getGymsFromStorage();
  const ownerGyms = currentUser.role === "admin" ? allGyms : allGyms.filter(g => g.ownerId === currentUser.id || g.ownerId === "owner_1");
  const allBookings = getBookingsFromStorage();

  const ownerGymIds = ownerGyms.map(g => g.id);
  const ownerBookings = allBookings.filter(b => ownerGymIds.includes(b.gymId));

  const totalGymsEl = document.getElementById("ownerStatTotalGyms");
  const activeListingsEl = document.getElementById("ownerStatActiveListings");
  const totalBookingsEl = document.getElementById("ownerStatTotalBookings");

  if (totalGymsEl) totalGymsEl.textContent = ownerGyms.length;
  if (activeListingsEl) activeListingsEl.textContent = ownerGyms.filter(g => g.status === "approved").length;
  if (totalBookingsEl) totalBookingsEl.textContent = ownerBookings.length;
}

function renderOwnerGyms() {
  const tableBody = document.getElementById("ownerGymsTableBody");
  if (!tableBody) return;

  const currentUser = getCurrentUser();
  const allGyms = getGymsFromStorage();
  const ownerGyms = currentUser.role === "admin" ? allGyms : allGyms.filter(g => g.ownerId === currentUser.id || g.ownerId === "owner_1");

  if (ownerGyms.length === 0) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
          <i class="fas fa-store-slash" style="font-size: 2rem; color: var(--accent-orange); margin-bottom: 10px;"></i>
          <p>No gym listings added yet. Click "Add New Gym" to submit your gym in Jamshedpur.</p>
        </td>
      </tr>
    `;
    return;
  }

  tableBody.innerHTML = ownerGyms.map(gym => `
    <tr>
      <td>
        <div style="display: flex; align-items: center; gap: 12px;">
          <img src="${gym.image}" alt="${gym.name}" style="width: 50px; height: 50px; border-radius: var(--radius-md); object-fit: cover;">
          <div>
            <strong style="color: var(--text-white);">${gym.name}</strong>
            <div style="font-size: 0.75rem; color: var(--text-muted);">${gym.id}</div>
          </div>
        </div>
      </td>
      <td><i class="fas fa-map-marker-alt" style="color: var(--accent-orange);"></i> ${gym.location}</td>
      <td><strong>₹${gym.price}</strong> / mo</td>
      <td>
        <span class="badge ${gym.status === 'approved' ? 'badge-success' : 'badge-warning'}">
          ${gym.status === 'approved' ? 'Active' : 'Pending Approval'}
        </span>
      </td>
      <td>${gym.timings}</td>
      <td>
        <div style="display: flex; gap: 8px;">
          <button onclick="editOwnerGym('${gym.id}')" class="btn btn-outline btn-sm"><i class="fas fa-edit"></i> Edit</button>
          <button onclick="deleteOwnerGym('${gym.id}')" class="btn btn-outline btn-sm" style="color: var(--danger-color); border-color: var(--danger-color);"><i class="fas fa-trash"></i> Delete</button>
        </div>
      </td>
    </tr>
  `).join("");
}

function openAddGymModal() {
  const modal = document.getElementById("ownerGymModal");
  const form = document.getElementById("ownerGymForm");
  const title = document.getElementById("modalFormTitle");

  if (form) form.reset();
  document.getElementById("ownerGymId").value = "";
  ownerImageBase64 = "";
  const preview = document.getElementById("gymImagePreview");
  if (preview) preview.style.display = "none";

  if (title) title.textContent = "Add New Gym Listing";
  if (modal) modal.classList.add("active");
}

function editOwnerGym(gymId) {
  const gym = getGymById(gymId);
  if (!gym) return;

  document.getElementById("ownerGymId").value = gym.id;
  document.getElementById("ownerGymName").value = gym.name;
  document.getElementById("ownerGymLocation").value = gym.location;
  document.getElementById("ownerGymAddress").value = gym.address;
  document.getElementById("ownerMonthlyPrice").value = gym.price;
  document.getElementById("ownerQuarterlyPrice").value = gym.plans?.quarterly || gym.price * 3 * 0.9;
  document.getElementById("ownerHalfYearlyPrice").value = gym.plans?.halfYearly || gym.price * 6 * 0.85;
  document.getElementById("ownerYearlyPrice").value = gym.plans?.yearly || gym.price * 12 * 0.75;
  document.getElementById("ownerGymTimings").value = gym.timings;
  document.getElementById("ownerGymOpeningDays").value = gym.openingDays || "Mon - Sat";
  document.getElementById("ownerGymDesc").value = gym.description || "";
  document.getElementById("ownerGymPhone").value = gym.phone || "";
  document.getElementById("ownerGymEmail").value = gym.email || "";
  document.getElementById("ownerGymImageUrl").value = gym.image || "";

  // Set facility checkboxes
  const facilityCheckboxes = document.querySelectorAll(".owner-facility-checkbox");
  facilityCheckboxes.forEach(cb => {
    cb.checked = gym.facilities ? gym.facilities.includes(cb.value) : false;
  });

  const preview = document.getElementById("gymImagePreview");
  if (preview && gym.image) {
    preview.src = gym.image;
    preview.style.display = "block";
  }

  const title = document.getElementById("modalFormTitle");
  if (title) title.textContent = "Edit Gym Listing";

  const modal = document.getElementById("ownerGymModal");
  if (modal) modal.classList.add("active");
}

function deleteOwnerGym(gymId) {
  if (confirm("Are you sure you want to delete this gym listing? This action cannot be undone.")) {
    deleteGymListing(gymId);
    showToast("Gym listing deleted successfully.", "info");
    renderOwnerStats();
    renderOwnerGyms();
  }
}

function closeOwnerModal() {
  const modal = document.getElementById("ownerGymModal");
  if (modal) modal.classList.remove("active");
}
