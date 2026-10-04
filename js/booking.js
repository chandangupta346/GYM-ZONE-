/* ==========================================================================
   GYMZONE - Booking Management Service
   ========================================================================== */

function getBookingsFromStorage() {
  const data = localStorage.getItem("gymzone_bookings");
  if (!data) return [];
  try {
    return JSON.parse(data);
  } catch (e) {
    return [];
  }
}

function saveBookingsToStorage(bookings) {
  localStorage.setItem("gymzone_bookings", JSON.stringify(bookings));
}

function createBooking(bookingData) {
  const bookings = getBookingsFromStorage();
  const newBooking = {
    id: "BK_" + Math.floor(100000 + Math.random() * 900000),
    ...bookingData,
    status: "Confirmed",
    createdDate: new Date().toISOString().split("T")[0]
  };

  bookings.unshift(newBooking);
  saveBookingsToStorage(bookings);
  return newBooking;
}

function cancelBooking(bookingId) {
  let bookings = getBookingsFromStorage();
  const booking = bookings.find(b => b.id === bookingId);
  if (booking) {
    booking.status = "Cancelled";
    saveBookingsToStorage(bookings);
    return true;
  }
  return false;
}

document.addEventListener("DOMContentLoaded", () => {
  const bookingForm = document.getElementById("trialBookingForm");
  const gymSelect = document.getElementById("bookingGymSelect");
  const bookingDateInput = document.getElementById("bookingDate");

  // Pre-set minimum date to today
  if (bookingDateInput) {
    const today = new Date().toISOString().split("T")[0];
    bookingDateInput.setAttribute("min", today);
  }

  // Populate Gym select dropdown if on booking page
  if (gymSelect) {
    const gyms = getGymsFromStorage().filter(g => g.status === "approved");
    gymSelect.innerHTML = `<option value="">-- Select a Gym in Jamshedpur --</option>` +
      gyms.map(g => `<option value="${g.id}">${g.name} (${g.location})</option>`).join("");

    // Auto-select gym if URL param passed
    const urlParams = new URLSearchParams(window.location.search);
    const preselectedGymId = urlParams.get("gymId");
    if (preselectedGymId) {
      gymSelect.value = preselectedGymId;
    }
  }

  // Pre-fill user details if logged in
  const currentUser = getCurrentUser();
  if (currentUser) {
    const nameInput = document.getElementById("bookingName");
    const phoneInput = document.getElementById("bookingPhone");
    const emailInput = document.getElementById("bookingEmail");

    if (nameInput) nameInput.value = currentUser.name;
    if (phoneInput && currentUser.phone) phoneInput.value = currentUser.phone;
    if (emailInput) emailInput.value = currentUser.email;
  }

  // Handle Trial Booking Form Submission
  if (bookingForm) {
    bookingForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const name = document.getElementById("bookingName")?.value.trim();
      const phone = document.getElementById("bookingPhone")?.value.trim();
      const email = document.getElementById("bookingEmail")?.value.trim();
      const gymId = document.getElementById("bookingGymSelect")?.value;
      const date = document.getElementById("bookingDate")?.value;
      const timeSlot = document.getElementById("bookingTimeSlot")?.value;

      // Reset errors
      document.querySelectorAll(".error-text").forEach(el => el.classList.remove("show"));

      let isValid = true;
      if (!name) {
        showFieldError("bookingNameError", "Please enter your full name.");
        isValid = false;
      }

      const phoneRegex = /^[0-9]{10}$/;
      const cleanPhone = phone ? phone.replace(/[^0-9]/g, "") : "";
      if (!phone || cleanPhone.length < 10) {
        showFieldError("bookingPhoneError", "Please enter a valid 10-digit mobile number.");
        isValid = false;
      }

      if (!gymId) {
        showFieldError("bookingGymError", "Please select a gym for your trial session.");
        isValid = false;
      }

      if (!date) {
        showFieldError("bookingDateError", "Please select a valid date.");
        isValid = false;
      }

      if (!timeSlot) {
        showFieldError("bookingTimeError", "Please select a time slot.");
        isValid = false;
      }

      if (!isValid) return;

      const selectedGym = getGymById(gymId);
      const bookingData = {
        userName: name,
        userPhone: phone,
        userEmail: email || currentUser?.email || "guest@gymzone.com",
        userId: currentUser?.id || "guest",
        gymId: selectedGym.id,
        gymName: selectedGym.name,
        gymLocation: selectedGym.location,
        gymImage: selectedGym.image,
        date: date,
        timeSlot: timeSlot
      };

      const newBooking = createBooking(bookingData);

      // Show modal confirmation or success UI
      const modal = document.getElementById("bookingSuccessModal");
      const confirmDetails = document.getElementById("bookingConfirmationDetails");

      if (confirmDetails) {
        confirmDetails.innerHTML = `
          <div style="background: var(--bg-surface); padding: 15px; border-radius: var(--radius-md); border: 1px solid var(--border-color); text-align: left; margin-top: 15px;">
            <p><strong>Booking ID:</strong> <span style="color: var(--accent-orange);">${newBooking.id}</span></p>
            <p><strong>Gym:</strong> ${newBooking.gymName} (${newBooking.gymLocation})</p>
            <p><strong>Name:</strong> ${newBooking.userName}</p>
            <p><strong>Phone:</strong> ${newBooking.userPhone}</p>
            <p><strong>Date & Time:</strong> ${newBooking.date} | ${newBooking.timeSlot}</p>
            <p><strong>Status:</strong> <span class="badge badge-success">Confirmed</span></p>
          </div>
        `;
      }

      if (modal) {
        modal.classList.add("active");
      } else {
        showToast("Trial booking confirmed successfully!", "success");
      }

      bookingForm.reset();
      renderUserBookings();
    });
  }

  // Render User Bookings if on Booking or Profile Page
  renderUserBookings();
});

function showFieldError(elementId, message) {
  const el = document.getElementById(elementId);
  if (el) {
    el.textContent = message;
    el.classList.add("show");
  }
}

function renderUserBookings() {
  const bookingsListContainer = document.getElementById("userBookingsList");
  if (!bookingsListContainer) return;

  const currentUser = getCurrentUser();
  const allBookings = getBookingsFromStorage();

  // Filter bookings for current user or all if guest
  const userBookings = currentUser 
    ? allBookings.filter(b => b.userId === currentUser.id || b.userEmail === currentUser.email)
    : allBookings.slice(0, 5); // Show recent bookings if guest

  if (userBookings.length === 0) {
    bookingsListContainer.innerHTML = `
      <div class="empty-state">
        <i class="fas fa-calendar-times empty-icon"></i>
        <h3 class="empty-title">No Bookings Found</h3>
        <p class="empty-desc">You haven't booked any free trial gym sessions yet.</p>
        <a href="explore.html" class="btn btn-primary"><i class="fas fa-dumbbell"></i> Explore Gyms & Book Now</a>
      </div>
    `;
    return;
  }

  bookingsListContainer.innerHTML = userBookings.map(bk => `
    <div class="review-card" style="display: flex; gap: 20px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
      <div style="display: flex; gap: 15px; align-items: center;">
        <img src="${bk.gymImage}" alt="${bk.gymName}" style="width: 70px; height: 70px; border-radius: var(--radius-md); object-fit: cover;">
        <div>
          <h4 style="font-size: 1.1rem; font-weight: 700;">${bk.gymName}</h4>
          <p style="font-size: 0.85rem; color: var(--text-muted);"><i class="fas fa-map-marker-alt" style="color: var(--accent-orange);"></i> ${bk.gymLocation}</p>
          <p style="font-size: 0.85rem; color: var(--text-white); margin-top: 4px;">
            <i class="far fa-calendar"></i> ${bk.date} | <i class="far fa-clock"></i> ${bk.timeSlot}
          </p>
        </div>
      </div>
      <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
        <span class="badge ${bk.status === 'Confirmed' ? 'badge-success' : 'badge-danger'}">${bk.status}</span>
        <span style="font-size: 0.75rem; color: var(--text-muted);">Ref: ${bk.id}</span>
        ${bk.status === 'Confirmed' ? `<button onclick="handleCancelBooking('${bk.id}')" class="btn btn-outline btn-sm" style="color: var(--danger-color); border-color: var(--danger-color);"><i class="fas fa-times"></i> Cancel</button>` : ''}
      </div>
    </div>
  `).join("");
}

function handleCancelBooking(bookingId) {
  if (confirm("Are you sure you want to cancel this free trial booking?")) {
    if (cancelBooking(bookingId)) {
      showToast("Booking cancelled.", "info");
      renderUserBookings();
    }
  }
}
