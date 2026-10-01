/* ==========================================================================
   VenueVibe Notifications System - Modular Controller
   Pure JavaScript, zero inline scripts or onclick attributes.
   ========================================================================== */

const NOTIFS_STORAGE_KEY = "venuevibe_notifications_v1";

const DEFAULT_NOTIFICATIONS = [
  {
    id: 1,
    category: 'booking',
    type: 'approved',
    title: 'Booking Approved: Multi Purpose Hall',
    message: 'Your venue requisition for the Fall Semester Cultural Showcase has been officially approved by Campus Administration.',
    venue: 'Multi Purpose Hall',
    date: 'Oct 12, 2026 • 04:00 PM - 08:00 PM',
    time: '10 mins ago',
    unread: true,
    refId: 'VV-REQ-8842'
  },
  {
    id: 2,
    category: 'booking',
    type: 'rejected',
    title: 'Booking Request Declined: VLSI LAB 2',
    message: 'Your lab reservation request was declined due to mandatory hardware maintenance and calibration during requested hours.',
    venue: 'VLSI LAB 2',
    date: 'Oct 18, 2026',
    time: '45 mins ago',
    unread: true,
    refId: 'VV-REQ-8819'
  },
  {
    id: 3,
    category: 'booking',
    type: 'pending',
    title: 'Booking Requisition Received: Computer Lab 3',
    message: 'Your booking submission is currently under review by Professor Shakib Khan (Faculty Lead).',
    venue: 'Computer Lab 3',
    date: 'Oct 22, 2026',
    time: '2 hours ago',
    unread: true,
    refId: 'VV-REQ-8901'
  },
  {
    id: 4,
    category: 'announcement',
    type: 'announcement',
    title: 'Campus Tech Fest 2026 Venue Guidelines Released',
    message: 'All student organization leads must submit equipment requisition lists 5 business days prior to event commencement.',
    venue: 'Horizon Hall & Amphitheater',
    date: 'Nov 01, 2026',
    time: '5 hours ago',
    unread: false,
    refId: 'VV-ANN-104'
  },
  {
    id: 5,
    category: 'system',
    type: 'system',
    title: 'Scheduled Electrical Grid Maintenance',
    message: 'Central Block B campus electrical grid will undergo routine maintenance on Oct 15 between 02:00 AM and 06:00 AM.',
    venue: 'Central Campus Block B',
    date: 'Oct 15, 2026',
    time: '1 day ago',
    unread: false,
    refId: 'VV-SYS-552'
  },
  {
    id: 6,
    category: 'booking',
    type: 'approved',
    title: 'Booking Approved: Pinnacle Seminar Suite 4B',
    message: 'Your reservation for the Departmental Guest Lecture has been confirmed. Entry pass generated.',
    venue: 'Pinnacle Seminar Suite 4B',
    date: 'Oct 11, 2026',
    time: '2 days ago',
    unread: false,
    refId: 'VV-REQ-8730'
  },
  {
    id: 7,
    category: 'announcement',
    type: 'announcement',
    title: 'November 2026 Reservation Windows Opened',
    message: 'Auditorium and Seminar Hall reservation slots for November 2026 are now open for student organizations on VenueVibe.',
    venue: 'All Campus Venues',
    date: 'Nov 2026 Slots',
    time: '3 days ago',
    unread: false,
    refId: 'VV-ANN-098'
  },
  {
    id: 8,
    category: 'booking',
    type: 'rejected',
    title: 'Booking Rejected: Lakeside Memorial Amphitheater',
    message: 'Requisition overlap with the Annual Sports Club Inauguration Ceremony.',
    venue: 'Lakeside Memorial Amphitheater',
    date: 'Oct 09, 2026',
    time: '4 days ago',
    unread: false,
    refId: 'VV-REQ-8612'
  }
];

function loadNotifications() {
  const raw = localStorage.getItem(NOTIFS_STORAGE_KEY);
  if (!raw) return DEFAULT_NOTIFICATIONS;
  try {
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) && parsed.length ? parsed : DEFAULT_NOTIFICATIONS;
  } catch (e) {
    return DEFAULT_NOTIFICATIONS;
  }
}

function saveNotifications(data) {
  localStorage.setItem(NOTIFS_STORAGE_KEY, JSON.stringify(data));
}

document.addEventListener("DOMContentLoaded", () => {
  let notifications = loadNotifications();
  let currentFilter = "all";
  let searchQuery = "";

  // Synchronize session details if provided by PHP
  if (window.USER_SESSION) {
    const s = window.USER_SESSION;
    const cLetter = document.getElementById("capsuleAvatarLetter");
    const cName = document.getElementById("capsuleUserName");
    const cRole = document.getElementById("capsuleUserRole");
    if (cLetter) cLetter.textContent = s.avatarLetter;
    if (cName) cName.textContent = s.userName;
    if (cRole) cRole.textContent = s.userRole;

    const mLetter = document.getElementById("profileModalAvatarLetter");
    const mName = document.getElementById("profileModalUserName");
    const mRole = document.getElementById("profileModalUserRole");
    const mId = document.getElementById("profileModalStudentId");
    const mEmail = document.getElementById("profileModalEmail");
    if (mLetter) mLetter.textContent = s.avatarLetter;
    if (mName) mName.textContent = s.userName;
    if (mRole) mRole.textContent = `${s.userRole} / ${s.department}`;
    if (mId) mId.textContent = s.studentId;
    if (mEmail) mEmail.textContent = s.userEmail;
  }

  // Live sync with MySQL via PHP API
  fetch("notifications.php?api=list")
    .then(res => res.json())
    .then(data => {
      if (data && data.status === "success" && Array.isArray(data.notifications) && data.notifications.length > 0) {
        notifications = data.notifications.map(n => {
          let category = "announcement";
          let type = n.type || "general";
          if (type === "approval" || type === "approved") {
            category = "booking";
            type = "approved";
          } else if (type === "rejection" || type === "rejected") {
            category = "booking";
            type = "rejected";
          } else if (type === "maintenance") {
            category = "system";
            type = "system";
          }

          let venue = "Campus Facility";
          if (n.title.indexOf(":") > -1) {
            venue = n.title.split(":")[1].trim();
          }

          return {
            id: Number(n.id),
            category: category,
            type: type,
            title: n.title,
            message: n.message,
            venue: venue,
            date: n.created_at ? n.created_at.substring(0, 10) : "Recent",
            time: n.time_ago || "Recent",
            unread: Number(n.is_read) === 0,
            refId: "VV-NOTIF-" + n.id
          };
        });
        saveNotifications(notifications);
        renderAll();
      }
    })
    .catch(() => {});

  // ---------- DOM Elements ----------
  const userNotificationList = document.getElementById("userNotificationList");
  const mainEmptyState = document.getElementById("mainEmptyState");

  const globalSearchInput = document.getElementById("globalSearchInput");
  const mainSearchInput = document.getElementById("mainSearchInput");

  const btnMarkAllRead = document.getElementById("btnMarkAllRead");
  const btnClearRead = document.getElementById("btnClearRead");

  const filterPillBtns = document.querySelectorAll(".tab-pill");
  const countAll = document.getElementById("countAll");
  const countUnread = document.getElementById("countUnread");
  const countBooking = document.getElementById("countBooking");
  const countAnnouncement = document.getElementById("countAnnouncement");
  const countSystem = document.getElementById("countSystem");

  const unreadMainBadge = document.getElementById("unreadMainBadge");
  const sidebarUnreadBadge = document.getElementById("sidebarUnreadBadge");

  // Sidebar mobile drawer
  const sidebar = document.getElementById("sidebar");
  const sidebarBackdrop = document.getElementById("sidebarBackdrop");
  const btnToggleSidebar = document.getElementById("btnToggleSidebar");

  // Details Modal
  const detailsModal = document.getElementById("detailsModal");
  const btnCloseDetailsModal = document.getElementById("btnCloseDetailsModal");
  const btnCloseDetailsBottom = document.getElementById("btnCloseDetailsBottom");
  const detailIconBg = document.getElementById("detailIconBg");
  const detailIcon = document.getElementById("detailIcon");
  const detailTitle = document.getElementById("detailTitle");
  const detailSubtitle = document.getElementById("detailSubtitle");
  const detailVenue = document.getElementById("detailVenue");
  const detailDate = document.getElementById("detailDate");
  const detailTime = document.getElementById("detailTime");
  const detailStatusBadge = document.getElementById("detailStatusBadge");
  const detailMessage = document.getElementById("detailMessage");

  // Settings Modal
  const settingsModal = document.getElementById("settingsModal");
  const btnOpenSettings = document.getElementById("btnOpenSettings");
  const btnCloseSettingsModal = document.getElementById("btnCloseSettingsModal");
  const btnCancelSettings = document.getElementById("btnCancelSettings");
  const btnSaveSettings = document.getElementById("btnSaveSettings");
  const settingSound = document.getElementById("settingSound");
  const settingToast = document.getElementById("settingToast");

  const toastContainer = document.getElementById("toastContainer");

  // Helper: Get Icon Name
  function getIconName(type) {
    switch(type) {
      case "approved": return "check-circle-2";
      case "rejected": return "x-circle";
      case "pending": return "clock";
      case "announcement": return "megaphone";
      default: return "info";
    }
  }

  // Helper: Status Badge HTML
  function getStatusBadgeHTML(type) {
    switch(type) {
      case "approved":
        return `<span class="status-badge approved"><i data-lucide="check"></i> Approved</span>`;
      case "rejected":
        return `<span class="status-badge rejected"><i data-lucide="x"></i> Declined</span>`;
      case "pending":
        return `<span class="status-badge pending"><i data-lucide="clock"></i> Under Review</span>`;
      case "announcement":
        return `<span class="status-badge announcement"><i data-lucide="megaphone"></i> Notice</span>`;
      default:
        return `<span class="status-badge system"><i data-lucide="info"></i> Maintenance</span>`;
    }
  }

  // Toast alert
  function showToast(msg, type = "info") {
    if (settingToast && !settingToast.checked && type === "info") return;
    if (!toastContainer) return;

    const toast = document.createElement("div");
    let icon = "info";
    if (type === "success") icon = "check-circle";
    if (type === "danger" || type === "error") icon = "alert-circle";

    toast.className = `toast-pill-alert ${type}`;
    toast.innerHTML = `<i data-lucide="${icon}"></i><span>${msg}</span>`;
    toastContainer.appendChild(toast);
    if (window.lucide) window.lucide.createIcons();

    setTimeout(() => toast.classList.add("show"), 10);
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 260);
    }, 3200);
  }

  // Web Audio Chime
  function playAudioChime() {
    if (settingSound && !settingSound.checked) return;
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      const now = ctx.currentTime;
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();

      osc.type = "sine";
      osc.frequency.setValueAtTime(523.25, now); // C5
      osc.frequency.exponentialRampToValueAtTime(659.25, now + 0.14); // E5

      gain.gain.setValueAtTime(0.12, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.28);

      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now);
      osc.stop(now + 0.28);
    } catch (e) {
      // Audio context policy fallback
    }
  }

  // Filter dataset
  function getFilteredData() {
    return notifications.filter(n => {
      let matchesFilter = true;
      if (currentFilter === "unread") matchesFilter = n.unread;
      else if (currentFilter === "booking") matchesFilter = (n.category === "booking");
      else if (currentFilter === "announcement") matchesFilter = (n.category === "announcement");
      else if (currentFilter === "system") matchesFilter = (n.category === "system");

      let matchesSearch = true;
      if (searchQuery.trim() !== "") {
        const q = searchQuery.toLowerCase();
        matchesSearch = n.title.toLowerCase().includes(q) ||
                        n.venue.toLowerCase().includes(q) ||
                        n.message.toLowerCase().includes(q) ||
                        n.refId.toLowerCase().includes(q);
      }
      return matchesFilter && matchesSearch;
    });
  }

  // Counters
  function updateCounters() {
    const unreadCount = notifications.filter(n => n.unread).length;
    const bookingCount = notifications.filter(n => n.category === "booking").length;
    const announcementCount = notifications.filter(n => n.category === "announcement").length;
    const systemCount = notifications.filter(n => n.category === "system").length;
    const allCount = notifications.length;

    if (countAll) countAll.textContent = allCount;
    if (countUnread) countUnread.textContent = unreadCount;
    if (countBooking) countBooking.textContent = bookingCount;
    if (countAnnouncement) countAnnouncement.textContent = announcementCount;
    if (countSystem) countSystem.textContent = systemCount;

    if (unreadMainBadge) unreadMainBadge.textContent = `${unreadCount} Unread`;
    if (sidebarUnreadBadge) sidebarUnreadBadge.textContent = unreadCount;

    filterPillBtns.forEach(pill => {
      pill.classList.toggle("active", pill.dataset.filter === currentFilter);
    });
  }

  // Render Main List
  function renderList() {
    const filtered = getFilteredData();
    if (!userNotificationList) return;

    if (filtered.length === 0) {
      userNotificationList.innerHTML = "";
      if (mainEmptyState) mainEmptyState.classList.add("show");
      return;
    }
    if (mainEmptyState) mainEmptyState.classList.remove("show");

    userNotificationList.innerHTML = filtered.map(item => `
      <div class="notif-row-item ${item.unread ? 'unread' : ''}" id="notif-row-${item.id}">
        <div class="notif-row-left">
          <div class="notif-icon-box bg-${item.type}">
            <i data-lucide="${getIconName(item.type)}"></i>
          </div>
          <div class="notif-body-col">
            <div class="notif-title-row">
              <span class="notif-title-text" data-action="details" data-id="${item.id}">
                ${item.title}
              </span>
              ${item.unread ? '<span class="unread-blue-dot"></span>' : ''}
            </div>
            <p class="notif-message-snippet">${item.message}</p>
            <div class="notif-meta-tags">
              <span class="meta-tag-item"><i data-lucide="map-pin"></i> ${item.venue}</span>
              <span>•</span>
              <span class="meta-tag-item"><i data-lucide="clock"></i> ${item.time}</span>
              <span>•</span>
              <span class="ref-code-badge">${item.refId}</span>
            </div>
          </div>
        </div>

        <div class="notif-row-right">
          ${getStatusBadgeHTML(item.type)}
          <div class="action-buttons-group">
            <button class="btn-row-action" data-action="toggle-read" data-id="${item.id}" title="${item.unread ? 'Mark as Read' : 'Mark as Unread'}">
              <i data-lucide="${item.unread ? 'mail-open' : 'mail'}"></i>
            </button>
            <button class="btn-row-action delete" data-action="delete" data-id="${item.id}" title="Delete notice">
              <i data-lucide="trash-2"></i>
            </button>
            <button class="btn-row-action" data-action="details" data-id="${item.id}" title="View details">
              <i data-lucide="chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    `).join("");

    if (window.lucide) window.lucide.createIcons();
  }

  function renderAll() {
    updateCounters();
    renderList();
  }

  // Details Modal
  function openDetails(id) {
    const item = notifications.find(n => n.id === Number(id));
    if (!item) return;

    item.unread = false;
    saveNotifications(notifications);
    renderAll();

    detailIconBg.className = `notif-icon-box bg-${item.type}`;
    detailIcon.setAttribute("data-lucide", getIconName(item.type));
    detailTitle.textContent = item.title;
    detailSubtitle.textContent = `Reference Code: ${item.refId}`;
    detailVenue.textContent = item.venue;
    detailDate.textContent = item.date;
    detailTime.textContent = item.time;
    detailMessage.textContent = item.message;
    detailStatusBadge.innerHTML = getStatusBadgeHTML(item.type);

    detailsModal.classList.add("show");
    if (window.lucide) window.lucide.createIcons();
  }

  function closeDetails() {
    if (detailsModal) detailsModal.classList.remove("show");
  }

  // Settings Modal
  function openSettings() {
    if (settingsModal) settingsModal.classList.add("show");
  }

  function closeSettings() {
    if (settingsModal) settingsModal.classList.remove("show");
  }

  // ---------- Event Listeners Attachment (Zero inline handlers) ----------

  // Filter pills
  filterPillBtns.forEach(pill => {
    pill.addEventListener("click", () => {
      currentFilter = pill.dataset.filter;
      renderAll();
    });
  });

  // Search input synchronization
  function handleSearch(val) {
    searchQuery = val;
    if (globalSearchInput && globalSearchInput.value !== val) globalSearchInput.value = val;
    if (mainSearchInput && mainSearchInput.value !== val) mainSearchInput.value = val;
    renderAll();
  }

  if (globalSearchInput) globalSearchInput.addEventListener("input", (e) => handleSearch(e.target.value));
  if (mainSearchInput) mainSearchInput.addEventListener("input", (e) => handleSearch(e.target.value));

  // Mark all read
  function markAllRead() {
    notifications.forEach(n => n.unread = false);
    saveNotifications(notifications);
    renderAll();
    showToast("All notifications marked as read", "success");
    fetch("notifications.php?action=mark_all_read", { method: "POST" }).catch(() => {});
  }

  if (btnMarkAllRead) btnMarkAllRead.addEventListener("click", markAllRead);

  // Clear read
  if (btnClearRead) {
    btnClearRead.addEventListener("click", () => {
      notifications = notifications.filter(n => n.unread);
      saveNotifications(notifications);
      renderAll();
      showToast("Cleared read notifications", "info");
    });
  }

  // Delegated clicks on notification items
  if (userNotificationList) {
    userNotificationList.addEventListener("click", (e) => {
      const target = e.target.closest("[data-action]");
      if (!target) return;
      const action = target.dataset.action;
      const id = Number(target.dataset.id);

      if (action === "details") {
        openDetails(id);
      } else if (action === "toggle-read") {
        const item = notifications.find(n => n.id === id);
        if (item) {
          item.unread = !item.unread;
          saveNotifications(notifications);
          renderAll();
        }
      } else if (action === "delete") {
        notifications = notifications.filter(n => n.id !== id);
        saveNotifications(notifications);
        renderAll();
        showToast("Notification deleted", "info");
      }
    });
  }



  // Mobile sidebar
  function toggleSidebar() {
    if (sidebar) sidebar.classList.toggle("open");
    if (sidebarBackdrop) sidebarBackdrop.classList.toggle("show");
  }

  if (btnToggleSidebar) btnToggleSidebar.addEventListener("click", toggleSidebar);
  if (sidebarBackdrop) sidebarBackdrop.addEventListener("click", toggleSidebar);

  // Details Modal close
  if (btnCloseDetailsModal) btnCloseDetailsModal.addEventListener("click", closeDetails);
  if (btnCloseDetailsBottom) btnCloseDetailsBottom.addEventListener("click", closeDetails);

  // Settings Modal
  if (btnOpenSettings) btnOpenSettings.addEventListener("click", openSettings);
  if (btnCloseSettingsModal) btnCloseSettingsModal.addEventListener("click", closeSettings);
  if (btnCancelSettings) btnCancelSettings.addEventListener("click", closeSettings);
  if (btnSaveSettings) {
    btnSaveSettings.addEventListener("click", () => {
      closeSettings();
      showToast("Notification preferences updated", "success");
    });
  }

  // Global Escape & Overlay Clicks
  document.querySelectorAll(".modal-overlay").forEach(overlay => {
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) {
        closeDetails();
        closeSettings();
      }
    });
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeDetails();
      closeSettings();
      if (sidebar && sidebar.classList.contains("open")) toggleSidebar();
    }
  });

  // 1. Automatically set current year in the footer
  const yearSpan = document.getElementById("year");
  if (yearSpan) {
    yearSpan.textContent = new Date().getFullYear();
  }

  // 2. Profile Modal Open / Close Logic
  const userBtn = document.getElementById("userBtn");
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
  if (closeProfileModal) closeProfileModal.addEventListener("click", closeProfile);
  if (profileLogoutBtn) {
    profileLogoutBtn.addEventListener("click", () => {
      closeProfile();
      showToast("Logged out successfully", "info");
    });
  }

  // Close modal when clicking the dark backdrop outside
  if (profileModalOverlay) {
    profileModalOverlay.addEventListener("click", (e) => {
      if (e.target === profileModalOverlay) closeProfile();
    });
  }

  // Close profile modal on Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeProfile();
  });

  // Initial render
  renderAll();
});
