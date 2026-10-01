/* =========================================================
   VenueVibe - User Dashboard JavaScript
   Enhanced Navbar, Interactive Calendar, and Functional Notifications
   ========================================================= */

// ---------- 1. Auto-set Current Year in Footer ----------
const yearSpan = document.getElementById("year");
if (yearSpan) {
  yearSpan.textContent = new Date().getFullYear();
}


// ---------- 2. Toast Popup Notification System ----------
let toastTimer;

function showToast(message) {
  const toast = document.getElementById("toast");
  if (!toast) return;

  clearTimeout(toastTimer);
  toast.textContent = message;
  toast.classList.add("show");

  toastTimer = setTimeout(() => {
    toast.classList.remove("show");
  }, 2500);
}

// Elements with [data-toast] show toast on click
document.querySelectorAll("[data-toast]").forEach(el => {
  el.addEventListener("click", e => {
    e.preventDefault();
    showToast(el.dataset.toast);
  });
});


// ---------- 3. Sidebar Navigation Active State ----------
const navLinks = document.querySelectorAll(".sidebar-nav .nav-link");

navLinks.forEach(link => {
  link.addEventListener("click", e => {
    const href = link.getAttribute("href");
    if (link.id === "sidebarNotificationsLink") {
      e.preventDefault();
      const noteSec = document.getElementById("notificationsSection");
      if (noteSec) {
        noteSec.scrollIntoView({ behavior: "smooth" });
        showToast("Scrolled to Recent Notifications");
      }
      return;
    }

    // If the link points to an actual page (like messages.html or notifications.html), allow normal browser navigation!
    if (href && href !== "#" && !href.startsWith("javascript:")) {
      return;
    }

    e.preventDefault();
    navLinks.forEach(l => l.classList.remove("active"));
    link.classList.add("active");
  });
});


// ---------- 4. FUNCTIONAL RECENT NOTIFICATIONS ----------
const notificationItems = document.querySelectorAll(".notification-item");
const countAllNotes = document.getElementById("countAllNotes");
const countUnreadNotes = document.getElementById("countUnreadNotes");
const filterTabs = document.querySelectorAll(".filter-tab");
const markAllReadBtn = document.getElementById("markAllReadBtn");

// Modals
const notificationModalOverlay = document.getElementById("notificationModalOverlay");
const modalNoteTitle = document.getElementById("modalNoteTitle");
const modalNoteTime = document.getElementById("modalNoteTime");
const modalNoteBody = document.getElementById("modalNoteBody");
const closeNoteModal = document.getElementById("closeNoteModal");
const btnNoteActionUnderstood = document.getElementById("btnNoteActionUnderstood");
const btnNoteActionDismiss = document.getElementById("btnNoteActionDismiss");
let activeOpenedNote = null;

// Recalculate badge counts
function updateNotificationCounters() {
  const allVisible = document.querySelectorAll(".notification-item");
  const unreadVisible = document.querySelectorAll(".notification-item.unread");

  if (countAllNotes) countAllNotes.textContent = allVisible.length;
  if (countUnreadNotes) countUnreadNotes.textContent = unreadVisible.length;
}

// Mark single note as read
function markNoteAsRead(item) {
  if (item && item.classList.contains("unread")) {
    item.classList.remove("unread");
    item.classList.remove("active-note");
    const dot = item.querySelector(".unread-dot");
    if (dot) dot.remove();
    updateNotificationCounters();
  }
}

// Mark all notes as read
function markAllNotesAsRead() {
  document.querySelectorAll(".notification-item.unread").forEach(item => {
    item.classList.remove("unread");
    item.classList.remove("active-note");
    const dot = item.querySelector(".unread-dot");
    if (dot) dot.remove();
  });
  updateNotificationCounters();
  showToast("All notifications marked as read ✔");
}

if (markAllReadBtn) markAllReadBtn.addEventListener("click", markAllNotesAsRead);

// Open Notification Details Modal
function openNoteDetails(item) {
  activeOpenedNote = item;
  markNoteAsRead(item);

  const title = item.dataset.title || item.querySelector("h4").textContent;
  const time = item.dataset.time || item.querySelector(".notification-time").textContent;
  const text = item.dataset.fulltext || item.querySelector("p").textContent;

  modalNoteTitle.textContent = title;
  modalNoteTime.textContent = time;
  modalNoteBody.textContent = text;

  if (notificationModalOverlay) {
    notificationModalOverlay.classList.add("open");
  }
}

// Attach click events to notification items
notificationItems.forEach(item => {
  item.addEventListener("click", e => {
    // If dismiss button clicked, remove item
    if (e.target.classList.contains("btn-note-dismiss")) {
      e.stopPropagation();
      item.style.opacity = "0";
      item.style.transform = "translateX(20px)";
      setTimeout(() => {
        item.remove();
        updateNotificationCounters();
        showToast("Notification dismissed");
      }, 200);
      return;
    }

    openNoteDetails(item);
  });
});

// Close Note Modal
function closeNotificationModal() {
  if (notificationModalOverlay) {
    notificationModalOverlay.classList.remove("open");
  }
}

if (closeNoteModal) closeNoteModal.addEventListener("click", closeNotificationModal);
if (btnNoteActionUnderstood) btnNoteActionUnderstood.addEventListener("click", closeNotificationModal);

if (btnNoteActionDismiss) {
  btnNoteActionDismiss.addEventListener("click", () => {
    closeNotificationModal();
    if (activeOpenedNote) {
      activeOpenedNote.remove();
      updateNotificationCounters();
      showToast("Notification dismissed");
    }
  });
}

// Filter Tabs: All vs Unread
filterTabs.forEach(tab => {
  tab.addEventListener("click", () => {
    filterTabs.forEach(t => t.classList.remove("active"));
    tab.classList.add("active");

    const filter = tab.dataset.filter;
    document.querySelectorAll(".notification-item").forEach(item => {
      if (filter === "all") {
        item.style.display = "flex";
      } else if (filter === "unread") {
        item.style.display = item.classList.contains("unread") ? "flex" : "none";
      }
    });
  });
});

// ---------- 5. CALENDAR VIEW (Dynamic Real-Time Calendar) ----------
const realDate = new Date();
let calMonth = realDate.getMonth(); // Current real-time month (0-indexed)
let calYear  = realDate.getFullYear(); // Current real-time year
const realTodayDay   = realDate.getDate();
const realTodayMonth = realDate.getMonth();
const realTodayYear  = realDate.getFullYear();

// Days with Events (Year-Month-Day : Event Type)
// Merges any server-provided bookings seamlessly
const eventSchedule = Object.assign({
  // Default sample bookings for the active month
  [`${calYear}-${calMonth}-${Math.min(realTodayDay + 3, 28)}`]: { type: "red",    title: "Annual Project Display", status: "Pending" },
  [`${calYear}-${calMonth}-${Math.min(realTodayDay + 7, 28)}`]: { type: "green",  title: "Campus Tech Seminar", status: "Confirmed" },
  [`${calYear}-${calMonth}-${Math.max(realTodayDay - 2, 2)}`]:  { type: "purple", title: "Club Workshop Session", status: "Booked" }
}, window.SERVER_CALENDAR_EVENTS || {});

const monthNames = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];

function renderCalendar() {
  const grid = document.getElementById("calendarGrid");
  const monthTitle = document.getElementById("calendarMonthTitle");

  if (!grid || !monthTitle) return;

  grid.innerHTML = "";
  monthTitle.textContent = `${monthNames[calMonth]} ${calYear}`;

  // Start weekday of 1st day of month (0 = Sun, 1 = Mon, ...)
  const startDay = new Date(calYear, calMonth, 1).getDay();
  // Total days in month
  const totalDays = new Date(calYear, calMonth + 1, 0).getDate();

  // Empty placeholder cells before day 1
  for (let i = 0; i < startDay; i++) {
    const emptyCell = document.createElement("div");
    emptyCell.className = "cal-cell empty";
    grid.appendChild(emptyCell);
  }

  // Generate day cells
  for (let day = 1; day <= totalDays; day++) {
    const cell = document.createElement("div");
    cell.className = "cal-cell";
    cell.textContent = day;

    const eventKey = `${calYear}-${calMonth}-${day}`;

    // Highlight today's real-time date!
    if (day === realTodayDay && calMonth === realTodayMonth && calYear === realTodayYear) {
      cell.classList.add("today");
    }

    // Attach colored event dot
    if (eventSchedule[eventKey]) {
      const dot = document.createElement("span");
      dot.className = `cal-event-dot dot-${eventSchedule[eventKey].type}`;
      cell.appendChild(dot);

      cell.title = `${eventSchedule[eventKey].title} (${eventSchedule[eventKey].status})`;
    }

    // Click handler on days
    cell.addEventListener("click", () => {
      document.querySelectorAll(".cal-cell").forEach(c => c.classList.remove("today"));
      cell.classList.add("today");

      if (eventSchedule[eventKey]) {
        showToast(`📅 ${monthNames[calMonth]} ${day}: ${eventSchedule[eventKey].title} [${eventSchedule[eventKey].status}]`);
      } else {
        showToast(`📅 ${monthNames[calMonth]} ${day}, ${calYear} (No bookings)`);
      }
    });

    grid.appendChild(cell);
  }
}

// Calendar Navigation Buttons
const prevMonthBtn = document.getElementById("prevMonthBtn");
const nextMonthBtn = document.getElementById("nextMonthBtn");

if (prevMonthBtn) {
  prevMonthBtn.addEventListener("click", () => {
    calMonth--;
    if (calMonth < 0) {
      calMonth = 11;
      calYear--;
    }
    renderCalendar();
  });
}

if (nextMonthBtn) {
  nextMonthBtn.addEventListener("click", () => {
    calMonth++;
    if (calMonth > 11) {
      calMonth = 0;
      calYear++;
    }
    renderCalendar();
  });
}


// ---------- 7. BOOKING DETAILS MODAL ----------
const bookingModalOverlay = document.getElementById("bookingModalOverlay");
const closeBookingModal = document.getElementById("closeBookingModal");
const closeBookingDetailBtn = document.getElementById("closeBookingDetailBtn");
const modalBookingTitle = document.getElementById("modalBookingTitle");
const modalBookingTime = document.getElementById("modalBookingTime");
const modalBookingPurpose = document.getElementById("modalBookingPurpose");
const modalBookingStatus = document.getElementById("modalBookingStatus");

document.querySelectorAll(".btn-details").forEach(btn => {
  btn.addEventListener("click", e => {
    e.preventDefault();
    if (modalBookingTitle) modalBookingTitle.textContent = btn.dataset.booking || "Venue Reservation";
    if (modalBookingTime) modalBookingTime.textContent = btn.dataset.time || "Jul 12, 2025 • 2:00 PM – 4:00 PM";
    if (modalBookingPurpose) modalBookingPurpose.textContent = btn.dataset.purpose || "Club Seminar";
    if (modalBookingStatus) {
      modalBookingStatus.textContent = btn.dataset.status || "Approved";
      modalBookingStatus.className = `status-pill ${btn.dataset.status === "Pending" ? "status-pending" : "status-approved"}`;
    }

    if (bookingModalOverlay) bookingModalOverlay.classList.add("open");
  });
});

function closeBookingModalFunc() {
  if (bookingModalOverlay) bookingModalOverlay.classList.remove("open");
}

if (closeBookingModal) closeBookingModal.addEventListener("click", closeBookingModalFunc);
if (closeBookingDetailBtn) closeBookingDetailBtn.addEventListener("click", closeBookingModalFunc);


// ---------- 8. USER PROFILE MODAL ----------
const userBtn = document.getElementById("userBtn");
const sidebarProfileLink = document.getElementById("sidebarProfileLink");
const profileModalOverlay = document.getElementById("profileModalOverlay");
const closeProfileModal = document.getElementById("closeProfileModal");
const profileLogoutBtn = document.getElementById("profileLogoutBtn");

function openProfile() {
  if (profileModalOverlay) profileModalOverlay.classList.add("open");
}

function closeProfile() {
  if (profileModalOverlay) profileModalOverlay.classList.remove("open");
}

if (userBtn) userBtn.addEventListener("click", openProfile);
if (sidebarProfileLink) sidebarProfileLink.addEventListener("click", e => { e.preventDefault(); openProfile(); });
if (closeProfileModal) closeProfileModal.addEventListener("click", closeProfile);

if (profileModalOverlay) {
  profileModalOverlay.addEventListener("click", e => {
    if (e.target === profileModalOverlay) closeProfile();
  });
}

// ---------- 8.1 EDIT PROFILE MODAL ----------
const btnOpenEditProfile = document.getElementById("btnOpenEditProfile");
const editProfileModalOverlay = document.getElementById("editProfileModalOverlay");
const closeEditProfileModal = document.getElementById("closeEditProfileModal");
const btnCancelEditProfile = document.getElementById("btnCancelEditProfile");
const editProfileForm = document.getElementById("editProfileForm");

function openEditProfile() {
  closeProfile();
  if (editProfileModalOverlay) editProfileModalOverlay.classList.add("open");
}

function closeEditProfile() {
  if (editProfileModalOverlay) editProfileModalOverlay.classList.remove("open");
}

if (btnOpenEditProfile) btnOpenEditProfile.addEventListener("click", openEditProfile);
if (closeEditProfileModal) closeEditProfileModal.addEventListener("click", closeEditProfile);
if (btnCancelEditProfile) btnCancelEditProfile.addEventListener("click", closeEditProfile);

if (editProfileModalOverlay) {
  editProfileModalOverlay.addEventListener("click", e => {
    if (e.target === editProfileModalOverlay) closeEditProfile();
  });
}

if (editProfileForm) {
  editProfileForm.addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(editProfileForm);
    const saveBtn = document.getElementById("btnSaveEditProfile");
    if (saveBtn) saveBtn.textContent = "Saving...";

    fetch("update-profile.php", {
      method: "POST",
      headers: { "Accept": "application/json" },
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast("✓ Profile updated successfully!");
        closeEditProfile();
        // Update DOM elements on page
        const d = data.data;
        const nameEl = document.querySelector(".capsule-name");
        if (nameEl) nameEl.textContent = d.name;
        const roleEl = document.querySelector(".capsule-role");
        if (roleEl) roleEl.textContent = d.role;
        const profName = document.querySelector(".profile-name");
        if (profName) profName.textContent = d.name;
        const profRole = document.querySelector(".profile-role");
        if (profRole) profRole.textContent = d.role + " / " + d.department;
        const sid = document.getElementById("profileDetailStudentId");
        if (sid) sid.textContent = d.student_id;
        const em = document.getElementById("profileDetailEmail");
        if (em) em.textContent = d.email;
        const dep = document.getElementById("profileDetailDepartment");
        if (dep) dep.textContent = d.department;
        const welcomeTitle = document.querySelector(".welcome-title");
        if (welcomeTitle) welcomeTitle.innerHTML = `Welcome back, <span class="accent-text">${d.name}</span>`;
      } else {
        showToast(data.message || "Failed to update profile.");
      }
    })
    .catch(() => {
      editProfileForm.submit();
    })
    .finally(() => {
      if (saveBtn) saveBtn.textContent = "Save Changes";
    });
  });
}

if (bookingModalOverlay) {
  bookingModalOverlay.addEventListener("click", e => {
    if (e.target === bookingModalOverlay) closeBookingModalFunc();
  });
}

if (notificationModalOverlay) {
  notificationModalOverlay.addEventListener("click", e => {
    if (e.target === notificationModalOverlay) closeNotificationModal();
  });
}

// Global Escape Key Listener for Modals
document.addEventListener("keydown", e => {
  if (e.key === "Escape") {
    closeProfile();
    closeEditProfile();
    closeBookingModalFunc();
    closeNotificationModal();
  }
});


// ---------- 9. Logout Action ----------
if (profileLogoutBtn) {
  profileLogoutBtn.addEventListener("click", () => {
    closeProfile();
    window.location.href = "logout.php";
  });
}

// ---------- 9b. Book a Venue Action Button ----------
const bookVenueActionBtn = document.getElementById("bookVenueActionBtn");
if (bookVenueActionBtn) {
  bookVenueActionBtn.addEventListener("click", () => {
    window.location.href = "venue-list.php";
  });
}

// ---------- 10. Live Messages Badge Sync with Storage ----------
function updateMessagesBadge() {
  const badge = document.getElementById("userMsgBadge");
  if (!badge) return;

  try {
    const raw = localStorage.getItem("venuevibe_messaging_v1");
    if (!raw) {
      badge.textContent = "1";
      badge.style.display = "grid";
      return;
    }
    const store = JSON.parse(raw);
    const activeStudentId = localStorage.getItem("venuevibe_active_student") || "fahim";
    const thread = (store.threads && store.threads[activeStudentId]) || [];
    // Count unread messages received from admin
    const unreadFromAdmin = thread.filter(m => m.sender === "admin" && !m.read).length;
    if (unreadFromAdmin > 0) {
      badge.textContent = unreadFromAdmin;
      badge.style.display = "grid";
    } else {
      badge.style.display = "none";
    }
  } catch (err) {
    badge.style.display = "none";
  }
}

window.addEventListener("storage", (e) => {
  if (e.key === "venuevibe_messaging_v1" || e.key === "venuevibe_active_student") {
    updateMessagesBadge();
  }
});

// Initial renders
renderCalendar();
updateNotificationCounters();
updateMessagesBadge();