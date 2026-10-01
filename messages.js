/* ==========================================================================
   VenueVibe Unified Messaging Controller
   Combines Student Inbox (students can message ONLY the Admin) and
   Admin Center (Admin can view who is messaging them and reply back)
   into a single, clean, cohesive application with zero complexity.
   ========================================================================== */

const STORAGE_KEY = "venuevibe_messaging_v1";
const ROLE_KEY = "venuevibe_current_role";
const ACTIVE_STUDENT_KEY = "venuevibe_active_student";

const ADMIN_PROFILE = {
  id: "admin",
  name: "Admin - Campus Center",
  role: "Facilities Coordinator",
  avatar: "" // No profile picture - automatically shows letter initial 'A'
};

// Seed dataset
function defaultStore() {
  return {
    users: {
      fahim: {
        name: "Fahim",
        role: "Event Organizer",
        avatar: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=120&q=80",
        isOnline: true
      },
      fahmida: {
        name: "Fahmida Galiba",
        role: "Faculty Advisor",
        avatar: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80",
        isOnline: false
      },
      akik: {
        name: "Akik Salim",
        role: "Student Event Lead",
        avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=120&q=80",
        isOnline: false
      },
      sabiha: {
        name: "Sabiha Sultana Barna",
        role: "Safety & Compliance Officer",
        avatar: "https://images.unsplash.com/photo-1567532939604-b6b5b0db2604?auto=format&fit=crop&w=120&q=80",
        isOnline: true
      },
      sabbir: {
        name: "Sabbir Hossen",
        role: "Auditorium Stage Manager",
        avatar: "https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=120&q=80",
        isOnline: false
      }
    },
    admin: ADMIN_PROFILE,
    threads: {
      fahim: [
        { sender: "admin", text: "Hello! I saw your request for the Innovation Hub room booking this Friday. I just wanted to confirm the expected attendance.", time: "10:38 AM" },
        { sender: "user", text: "Hi! Yes, we're expecting around 25 students. We'll need the projector if possible.", time: "10:40 AM", read: true },
        { sender: "admin", text: "Sure, the Innovation Hub is available and the projector is built-in. I've updated your reservation to include AV support. You're all set!", time: "10:42 AM" }
      ],
      fahmida: [
        { sender: "user", text: "Is there any room available for our senior robotics project showcase next Wednesday afternoon?", time: "Yesterday", read: false }
      ],
      akik: [
        { sender: "user", text: "Hey! Did you submit the venue request for the Hackathon kickoff?", time: "Tue", read: true },
        { sender: "admin", text: "Received! We have noted this in your booking file.", time: "Tue" }
      ],
      sabiha: [
        { sender: "user", text: "Hi Admin, please note that for groups over 20 people in the Innovation Hub, fire exit pathways must remain unobstructed.", time: "Oct 24", read: false },
        { sender: "user", text: "Please review the safety guidelines and acknowledge before Friday.", time: "Oct 24", read: false }
      ],
      sabbir: [
        { sender: "admin", text: "Sabbir, we just received confirmation for the lighting setup for the rehearsal.", time: "Yesterday" },
        { sender: "user", text: "Thanks for the update on the booking.", time: "Yesterday", read: true }
      ]
    }
  };
}

function loadStore() {
  const raw = localStorage.getItem(STORAGE_KEY);
  if (!raw) {
    const seeded = defaultStore();
    localStorage.setItem(STORAGE_KEY, JSON.stringify(seeded));
    return seeded;
  }
  try {
    const parsed = JSON.parse(raw);
    if (!parsed.users || !parsed.threads) {
      const seeded = defaultStore();
      localStorage.setItem(STORAGE_KEY, JSON.stringify(seeded));
      return seeded;
    }
    return parsed;
  } catch (e) {
    const seeded = defaultStore();
    localStorage.setItem(STORAGE_KEY, JSON.stringify(seeded));
    return seeded;
  }
}

function saveStore(store) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(store));
}

function formatNow() {
  const now = new Date();
  const hours = now.getHours();
  const minutes = now.getMinutes().toString().padStart(2, "0");
  const ampm = hours >= 12 ? "PM" : "AM";
  return `${hours % 12 || 12}:${minutes} ${ampm}`;
}

document.addEventListener("DOMContentLoaded", () => {
  let store = loadStore();

  // Detect role from server session, URL query, or stored preference
  const urlParams = new URLSearchParams(window.location.search);
  let currentRole = (window.SERVER_SESSION && window.SERVER_SESSION.isAdmin)
    ? "admin"
    : (urlParams.get("role") || localStorage.getItem(ROLE_KEY) || "user");

  // Synchronize session details if provided by PHP
  if (window.SERVER_SESSION) {
    const s = window.SERVER_SESSION;
    const cLetter = document.getElementById("msgCapsuleAvatarLetter");
    const cName = document.getElementById("msgCapsuleUserName");
    const cRole = document.getElementById("msgCapsuleUserRole");
    if (cLetter) cLetter.textContent = s.avatarLetter;
    if (cName) cName.textContent = s.userName;
    if (cRole) cRole.textContent = s.userRole;

    const mLetter = document.getElementById("msgModalAvatarLetter");
    const mName = document.getElementById("msgModalUserName");
    const mRole = document.getElementById("msgModalUserRole");
    const mId = document.getElementById("msgModalStudentId");
    const mEmail = document.getElementById("msgModalEmail");
    if (mLetter) mLetter.textContent = s.avatarLetter;
    if (mName) mName.textContent = s.userName;
    if (mRole) mRole.textContent = `${s.userRole} / ${s.department}`;
    if (mId) mId.textContent = s.studentId;
    if (mEmail) mEmail.textContent = s.userEmail;
  }

  // Active student identity for student view
  let currentStudentId = localStorage.getItem(ACTIVE_STUDENT_KEY) || "fahim";
  if (!store.users[currentStudentId]) currentStudentId = "fahim";

  // Active user selection for admin view
  let adminActiveUserId = currentStudentId;
  let adminFilter = "all"; // "all" | "pending" | "unread"

  // Live fetch messages from MySQL via PHP API
  fetch("messages.php?api=messages")
    .then(res => res.json())
    .then(data => {
      if (data && data.status === "success" && Array.isArray(data.messages) && data.messages.length > 0) {
        const studentKey = currentRole === "user" ? currentStudentId : adminActiveUserId;
        store.threads[studentKey] = data.messages.map(m => ({
          sender: (Number(m.sender_id) === 1) ? "admin" : "user",
          text: m.text,
          time: m.time_sent || "Recent",
          read: Boolean(Number(m.is_read))
        }));
        saveStore(store);
        if (currentRole === "user") {
          renderStudentThread();
        } else {
          renderAdminConversation(adminActiveUserId);
        }
      }
    })
    .catch(() => {});

  // ---------- DOM Elements ----------
  const btnRoleStudent = document.getElementById("btnRoleStudent");
  const btnRoleAdmin = document.getElementById("btnRoleAdmin");

  const convPanel = document.getElementById("convPanel");
  const convListContainer = document.getElementById("convListContainer");
  const chatMessages = document.getElementById("chatMessages");
  const chatHeaderAvatar = document.getElementById("chatHeaderAvatar");
  const chatHeaderLetter = document.getElementById("chatHeaderLetter");
  const chatHeaderName = document.getElementById("chatHeaderName");
  const chatHeaderStatus = document.getElementById("chatHeaderStatus");
  const chatHeaderOnlineDot = document.getElementById("chatHeaderOnlineDot");
  const chatInput = document.getElementById("chatInput");
  const btnSend = document.getElementById("btnSend");
  const searchInput = document.getElementById("searchInput");
  const toastNotice = document.getElementById("toastNotice");
  const toastMessage = document.getElementById("toastMessage");
  const btnAttachment = document.getElementById("btnAttachment");
  const fileAttachmentInput = document.getElementById("fileAttachmentInput");
  const btnEmoji = document.getElementById("btnEmoji");
  const emojiTray = document.getElementById("emojiTray");
  const btnBackToConversations = document.getElementById("btnBackToConversations");

  // Filters & Counters
  const filterChips = document.querySelectorAll(".conv-filter-chip");
  const countAllThreads = document.getElementById("countAllThreads");
  const countPendingThreads = document.getElementById("countPendingThreads");
  const countUnreadThreads = document.getElementById("countUnreadThreads");

  // Student switcher
  const studentSelector = document.getElementById("studentSelector");
  const sidebarUserName = document.getElementById("sidebarUserName");
  const sidebarUserRole = document.getElementById("sidebarUserRole");
  const sidebarAvatarLetter = document.getElementById("sidebarAvatarLetter");

  // Modals
  const composeModal = document.getElementById("composeModal");
  const btnOpenCompose = document.getElementById("btnOpenCompose");
  const closeComposeModal = document.getElementById("closeComposeModal");
  const btnCancelCompose = document.getElementById("btnCancelCompose");
  const btnSendCompose = document.getElementById("btnSendCompose");
  const composeRecipient = document.getElementById("composeRecipient");
  const composeMessage = document.getElementById("composeMessage");

  const infoModal = document.getElementById("infoModal");
  const btnInfo = document.getElementById("btnInfo");
  const closeInfoModal = document.getElementById("closeInfoModal");
  const btnCloseInfoModalAction = document.getElementById("btnCloseInfoModalAction");
  const infoModalHeading = document.getElementById("infoModalHeading");
  const infoModalAvatar = document.getElementById("infoModalAvatar");
  const infoModalAvatarLetter = document.getElementById("infoModalAvatarLetter");
  const infoModalName = document.getElementById("infoModalName");
  const infoModalRole = document.getElementById("infoModalRole");
  const infoModalDetailsBody = document.getElementById("infoModalDetailsBody");

  const callModal = document.getElementById("callModal");
  const btnCall = document.getElementById("btnCall");
  const btnEndCall = document.getElementById("btnEndCall");
  const callModalTitle = document.getElementById("callModalTitle");

  // Quick Chips
  const quickChips = document.querySelectorAll(".quick-chip");

  function showToast(msg) {
    if (!toastNotice) return;
    toastMessage.textContent = msg;
    toastNotice.classList.add("show");
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => toastNotice.classList.remove("show"), 2600);
  }

  function scrollToBottom(smooth = true) {
    chatMessages.scrollTo({ top: chatMessages.scrollHeight, behavior: smooth ? "smooth" : "auto" });
  }

  function lastMessage(userId) {
    const thread = store.threads[userId] || [];
    return thread.length ? thread[thread.length - 1] : null;
  }

  function getUnreadCount(userId) {
    const thread = store.threads[userId] || [];
    return thread.filter(m => m.sender === "user" && !m.read).length;
  }

  function isAwaitingReply(userId) {
    const last = lastMessage(userId);
    return last && last.sender === "user";
  }

  // ---------- Role-Based Mode (Student vs Admin) ----------
  function setRole(role, notify = false) {
    currentRole = role;
    localStorage.setItem(ROLE_KEY, role);

    document.body.className = role === "admin" ? "mode-admin" : "mode-user";

    if (role === "user") {
      updateStudentUI();
      renderStudentThread();
      chatInput.placeholder = "Message Admin (Facilities Office)...";
      if (notify) showToast("Viewing as Student (Direct Message to Admin)");
    } else {
      updateFilterCounters();
      renderConversationList();
      renderAdminConversation(adminActiveUserId);
      chatInput.placeholder = "Reply to student...";
      if (notify) showToast("Viewing as Administrator (All Students)");
    }
  }

  function updateStudentUI() {
    const student = store.users[currentStudentId] || { name: "Student", role: "Student" };
    if (studentSelector) studentSelector.value = currentStudentId;
    if (sidebarUserName) sidebarUserName.textContent = student.name;
    if (sidebarUserRole) sidebarUserRole.textContent = student.role;
    if (sidebarAvatarLetter) sidebarAvatarLetter.textContent = student.name.charAt(0).toUpperCase();
  }

  function updateFilterCounters() {
    const userIds = Object.keys(store.users);
    let pendingCount = 0;
    let unreadCount = 0;

    userIds.forEach(uid => {
      if (isAwaitingReply(uid)) pendingCount++;
      if (getUnreadCount(uid) > 0) unreadCount++;
    });

    if (countAllThreads) countAllThreads.textContent = userIds.length;
    if (countPendingThreads) countPendingThreads.textContent = pendingCount;
    if (countUnreadThreads) countUnreadThreads.textContent = unreadCount;
  }

  // ---------- Render Conversation List (Admin Mode) ----------
  function renderConversationList() {
    convListContainer.innerHTML = "";
    const userIds = Object.keys(store.users);
    updateFilterCounters();

    const query = searchInput ? searchInput.value.toLowerCase().trim() : "";
    let displayedCount = 0;

    userIds.forEach(userId => {
      const user = store.users[userId];
      const last = lastMessage(userId);
      const unreadCount = getUnreadCount(userId);
      const awaiting = isAwaitingReply(userId);

      if (adminFilter === "pending" && !awaiting) return;
      if (adminFilter === "unread" && unreadCount === 0) return;

      const lastText = last ? last.text.toLowerCase() : "";
      if (query && !user.name.toLowerCase().includes(query) && !lastText.includes(query)) {
        return;
      }

      displayedCount++;

      const item = document.createElement("div");
      item.className = "conv-item" + (userId === adminActiveUserId ? " active" : "");
      item.dataset.id = userId;
      item.dataset.name = user.name;

      let statusBadge = "";
      if (unreadCount > 0) {
        statusBadge = `<span class="conv-badge">${unreadCount}</span>`;
      } else if (awaiting) {
        statusBadge = `<span class="awaiting-badge">Awaiting</span>`;
      } else if (last && last.sender === "admin") {
        statusBadge = `<span class="replied-badge">Replied</span>`;
      }

      item.innerHTML = `
        <div class="conv-avatar-wrap">
          <img class="conv-avatar-img" src="${user.avatar}" alt="${user.name}">
          ${user.isOnline ? '<span class="online-dot"></span>' : ""}
        </div>
        <div class="conv-content">
          <div class="conv-row-top">
            <span class="conv-name">${user.name}</span>
            <span class="conv-time">${last ? last.time : ""}</span>
          </div>
          <div class="conv-row-bottom">
            <span class="conv-preview">${last ? (last.sender === "admin" ? "You: " : "") + last.text : "No messages yet"}</span>
            ${statusBadge}
          </div>
        </div>
      `;

      item.addEventListener("click", () => {
        renderAdminConversation(userId);
        if (convPanel && window.innerWidth <= 768) {
          convPanel.classList.add("hidden-mobile");
        }
      });

      convListContainer.appendChild(item);
    });

    if (displayedCount === 0) {
      convListContainer.innerHTML = `
        <div style="padding: 32px 16px; text-align: center; color: var(--slate-400); font-size: 0.85rem;">
          <p>No student conversations found</p>
          <span style="font-size: 0.75rem;">Change filter or search term</span>
        </div>
      `;
    }
  }

  // ---------- Message Bubble Builder ----------
  function appendMessageDOM(msg, isUserMode) {
    const row = document.createElement("div");
    // In student mode: student's own messages are outgoing, admin is incoming.
    // In admin mode: admin's messages are outgoing, student's are incoming.
    const isOutgoing = isUserMode ? (msg.sender === "user") : (msg.sender === "admin");
    row.className = "msg-row " + (isOutgoing ? "outgoing" : "incoming");

    const isMsgFromAdmin = (msg.sender === "admin");
    let avatarMarkup = "";

    if (isMsgFromAdmin) {
      // Admin has NO profile picture - uses letter "A" automatically
      avatarMarkup = `<div class="msg-avatar avatar-letter" style="display:flex;align-items:center;justify-content:center;background:#6366f1;color:#ffffff;font-weight:700;font-size:12px;border-radius:50%;width:32px;height:32px;margin-bottom:20px;flex-shrink:0;">A</div>`;
    } else {
      const targetStudentId = isUserMode ? currentStudentId : adminActiveUserId;
      const student = store.users[targetStudentId] || { name: "Student", avatar: "" };
      if (student.avatar) {
        avatarMarkup = `<img class="msg-avatar" src="${student.avatar}" alt="">`;
      } else {
        const studentLetter = (student.name || "S").charAt(0).toUpperCase();
        avatarMarkup = `<div class="msg-avatar avatar-letter" style="display:flex;align-items:center;justify-content:center;background:#0ea5e9;color:#ffffff;font-weight:700;font-size:12px;border-radius:50%;width:32px;height:32px;margin-bottom:20px;flex-shrink:0;">${studentLetter}</div>`;
      }
    }

    row.innerHTML = `
      ${avatarMarkup}
      <div class="msg-body-col">
        <div class="msg-bubble">${msg.text}</div>
        <div class="msg-meta">
          <span>${msg.time}</span>
          ${isOutgoing && msg.read ? `<span class="read-receipt" title="Read"><svg viewBox="0 0 24 24"><path d="M18 6L7 17l-5-5"></path><path d="M22 10l-7.5 7.5L13 16"></path></svg></span>` : ""}
        </div>
      </div>
    `;
    chatMessages.appendChild(row);
  }

  // ---------- Render Student View Thread (Student messages Admin ONLY) ----------
  function renderStudentThread() {
    if (chatHeaderAvatar) chatHeaderAvatar.style.display = "none";
    if (chatHeaderLetter) {
      chatHeaderLetter.style.display = "flex";
      chatHeaderLetter.textContent = "A";
    }
    chatHeaderName.textContent = "Admin - Campus Center";
    chatHeaderStatus.innerHTML = '<span class="status-dot-sm"></span> Online';
    chatHeaderOnlineDot.style.display = "block";

    chatMessages.innerHTML = `
      <div class="user-inbox-policy-banner">
        <div class="policy-icon">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
          </svg>
        </div>
        <div class="policy-content">
          <span class="policy-title">Direct Line to Campus Administration</span>
          You can message only the Admin for venue bookings and room requests. Direct student-to-student messaging is restricted by campus policy.
        </div>
      </div>
      <div class="date-pill-divider"><span class="date-pill">TODAY</span></div>
    `;

    // Mark admin messages as read by student
    let updated = false;
    (store.threads[currentStudentId] || []).forEach(msg => {
      if (msg.sender === "admin" && !msg.read) {
        msg.read = true;
        updated = true;
      }
      appendMessageDOM(msg, true);
    });

    if (updated) saveStore(store);
    scrollToBottom(false);
  }

  // ---------- Render Admin View Conversation ----------
  function renderAdminConversation(userId) {
    const user = store.users[userId];
    if (!user) return;
    adminActiveUserId = userId;

    // Mark student messages as read when Admin opens the thread
    let updated = false;
    (store.threads[userId] || []).forEach(m => {
      if (m.sender === "user" && !m.read) {
        m.read = true;
        updated = true;
      }
    });
    if (updated) {
      saveStore(store);
      updateFilterCounters();
    }

    if (user.avatar) {
      if (chatHeaderAvatar) {
        chatHeaderAvatar.src = user.avatar;
        chatHeaderAvatar.style.display = "block";
      }
      if (chatHeaderLetter) chatHeaderLetter.style.display = "none";
    } else {
      if (chatHeaderAvatar) chatHeaderAvatar.style.display = "none";
      if (chatHeaderLetter) {
        chatHeaderLetter.style.display = "flex";
        chatHeaderLetter.textContent = (user.name || "U").charAt(0).toUpperCase();
      }
    }
    chatHeaderName.textContent = user.name;
    if (user.isOnline) {
      chatHeaderStatus.innerHTML = '<span class="status-dot-sm"></span> Online • Requisition Inquirer';
      chatHeaderOnlineDot.style.display = "block";
    } else {
      chatHeaderStatus.innerHTML = `<span style="color: var(--slate-400);">${user.role}</span>`;
      chatHeaderOnlineDot.style.display = "none";
    }

    chatMessages.innerHTML = `<div class="date-pill-divider"><span class="date-pill">TODAY</span></div>`;
    (store.threads[userId] || []).forEach(m => appendMessageDOM(m, false));
    scrollToBottom(false);

    document.querySelectorAll(".conv-item").forEach(el => {
      el.classList.toggle("active", el.dataset.id === userId);
    });
  }

  // ---------- Send Message Handler ----------
  function sendMessage() {
    const text = chatInput.value.trim();
    if (!text) return;

    if (currentRole === "user") {
      // Student sending to Admin
      const msg = { sender: "user", text, time: formatNow(), read: false };
      store.threads[currentStudentId] = store.threads[currentStudentId] || [];
      store.threads[currentStudentId].push(msg);
      saveStore(store);

      appendMessageDOM(msg, true);
      scrollToBottom(true);
      chatInput.value = "";

      // Post to PHP backend API
      fetch("messages.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ text: text })
      }).catch(() => {});
    } else {
      // Admin replying to active student
      if (!adminActiveUserId) return;
      const msg = { sender: "admin", text, time: formatNow(), read: false };
      store.threads[adminActiveUserId] = store.threads[adminActiveUserId] || [];
      store.threads[adminActiveUserId].push(msg);
      saveStore(store);

      appendMessageDOM(msg, false);
      scrollToBottom(true);
      chatInput.value = "";
      renderConversationList();

      // Post to PHP backend API
      fetch("messages.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ text: text, receiver_id: 2 })
      }).catch(() => {});
    }
  }

  // Send message
  btnSend.addEventListener("click", sendMessage);
  chatInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  // Filter chips in admin mode
  filterChips.forEach(chip => {
    chip.addEventListener("click", () => {
      filterChips.forEach(c => c.classList.remove("active"));
      chip.classList.add("active");
      adminFilter = chip.dataset.filter;
      renderConversationList();
    });
  });

  // Quick Chips (works in both Student & Admin modes)
  quickChips.forEach(chip => {
    chip.addEventListener("click", () => {
      const text = chip.dataset.quick;
      chatInput.value = text;
      chatInput.focus();
    });
  });

  // Search input in admin mode
  if (searchInput) {
    searchInput.addEventListener("input", () => {
      renderConversationList();
    });
  }

  // Mobile back to list
  if (btnBackToConversations && convPanel) {
    btnBackToConversations.addEventListener("click", () => {
      convPanel.classList.remove("hidden-mobile");
    });
  }

  // Emoji picker
  if (btnEmoji && emojiTray) {
    btnEmoji.addEventListener("click", (e) => {
      e.stopPropagation();
      emojiTray.classList.toggle("active");
    });
    document.addEventListener("click", (e) => {
      if (!emojiTray.contains(e.target) && e.target !== btnEmoji) emojiTray.classList.remove("active");
    });
    document.querySelectorAll(".emoji-item").forEach(item => {
      item.addEventListener("click", () => {
        chatInput.value += item.textContent;
        chatInput.focus();
        emojiTray.classList.remove("active");
      });
    });
  }

  // File attachment
  if (btnAttachment && fileAttachmentInput) {
    btnAttachment.addEventListener("click", () => fileAttachmentInput.click());
    fileAttachmentInput.addEventListener("change", () => {
      if (fileAttachmentInput.files.length > 0) {
        const file = fileAttachmentInput.files[0];
        const sender = currentRole === "user" ? "user" : "admin";
        const targetId = currentRole === "user" ? currentStudentId : adminActiveUserId;
        const msg = {
          sender,
          text: `📎 Attached: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`,
          time: formatNow(),
          read: false
        };
        store.threads[targetId].push(msg);
        saveStore(store);

        appendMessageDOM(msg, currentRole === "user");
        scrollToBottom(true);
        if (currentRole === "admin") renderConversationList();
        fileAttachmentInput.value = "";
        showToast("File attachment sent");
      }
    });
  }

  // Compose Modal (Admin Mode)
  if (btnOpenCompose && composeModal) {
    btnOpenCompose.addEventListener("click", () => {
      composeRecipient.innerHTML = Object.entries(store.users)
        .map(([id, u]) => `<option value="${id}">${u.name} — ${u.role}</option>`)
        .join("");
      composeModal.classList.add("active");
      if (composeMessage) composeMessage.focus();
    });
    const closeCompose = () => composeModal.classList.remove("active");
    if (closeComposeModal) closeComposeModal.addEventListener("click", closeCompose);
    if (btnCancelCompose) btnCancelCompose.addEventListener("click", closeCompose);
    if (btnSendCompose) {
      btnSendCompose.addEventListener("click", () => {
        const targetId = composeRecipient.value;
        const msgText = composeMessage.value.trim();
        if (!msgText) { showToast("Please write a message."); return; }
        closeCompose();
        composeMessage.value = "";
        adminActiveUserId = targetId;
        renderAdminConversation(targetId);
        chatInput.value = msgText;
        sendMessage();
        showToast(`Message sent to ${store.users[targetId].name}`);
      });
    }
  }

  // Details Modal (Info)
  if (btnInfo && infoModal) {
    btnInfo.addEventListener("click", () => {
      if (currentRole === "user") {
        // Show Admin info with letter avatar
        infoModalHeading.textContent = "Facilities Coordinator";
        if (infoModalAvatar) infoModalAvatar.style.display = "none";
        if (infoModalAvatarLetter) {
          infoModalAvatarLetter.style.display = "flex";
          infoModalAvatarLetter.textContent = "A";
        }
        infoModalName.textContent = store.admin.name;
        infoModalRole.textContent = "● " + store.admin.role;
        infoModalDetailsBody.innerHTML = `
          <p><strong>Department:</strong> Campus Facilities & Reservations Office</p>
          <p><strong>Location:</strong> Student Activity Center, Hall 204</p>
          <p><strong>Office Hours:</strong> Mon - Fri, 8:30 AM - 5:00 PM</p>
          <p><strong>Scope:</strong> Classroom, Lab & Auditorium Reservations</p>
        `;
      } else {
        // Show Student info
        const user = store.users[adminActiveUserId];
        if (user) {
          infoModalHeading.textContent = "Student Details";
          if (user.avatar) {
            if (infoModalAvatar) {
              infoModalAvatar.src = user.avatar;
              infoModalAvatar.style.display = "block";
            }
            if (infoModalAvatarLetter) infoModalAvatarLetter.style.display = "none";
          } else {
            if (infoModalAvatar) infoModalAvatar.style.display = "none";
            if (infoModalAvatarLetter) {
              infoModalAvatarLetter.style.display = "flex";
              infoModalAvatarLetter.textContent = (user.name || "U").charAt(0).toUpperCase();
            }
          }
          infoModalName.textContent = user.name;
          infoModalRole.textContent = "● " + user.role;
          infoModalDetailsBody.innerHTML = `
            <p><strong>Affiliation:</strong> United International University</p>
            <p><strong>Messaging Channel:</strong> Direct Administrator Line</p>
            <p><strong>Status:</strong> Active Requisition Inquirer</p>
          `;
        }
      }
      infoModal.classList.add("active");
    });
    const closeInfo = () => infoModal.classList.remove("active");
    if (closeInfoModal) closeInfoModal.addEventListener("click", closeInfo);
    if (btnCloseInfoModalAction) btnCloseInfoModalAction.addEventListener("click", closeInfo);
  }



  // Escape key & overlay close
  document.querySelectorAll(".modal-overlay").forEach(overlay => {
    overlay.addEventListener("click", (e) => { if (e.target === overlay) overlay.classList.remove("active"); });
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      document.querySelectorAll(".modal-overlay").forEach(o => o.classList.remove("active"));
      if (emojiTray) emojiTray.classList.remove("active");
    }
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "k" && currentRole === "admin") {
      e.preventDefault();
      if (searchInput) searchInput.focus();
    }
  });

  // Storage live-sync
  window.addEventListener("storage", (e) => {
    if (e.key === STORAGE_KEY) {
      store = loadStore();
      if (currentRole === "user") {
        renderStudentThread();
        showToast("New message received from Admin");
      } else {
        renderConversationList();
        renderAdminConversation(adminActiveUserId);
        showToast("New message received from student");
      }
    }
    if (e.key === ACTIVE_STUDENT_KEY && currentRole === "user") {
      currentStudentId = localStorage.getItem(ACTIVE_STUDENT_KEY) || "fahim";
      updateStudentUI();
      renderStudentThread();
    }
  });

  // Footer Year
  const yearSpan = document.getElementById("year");
  if (yearSpan) {
    yearSpan.textContent = new Date().getFullYear();
  }

  // Profile Modal Open / Close Logic
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
      showToast("Logged out successfully");
    });
  }

  if (profileModalOverlay) {
    profileModalOverlay.addEventListener("click", (e) => {
      if (e.target === profileModalOverlay) closeProfile();
    });
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeProfile();
  });

  // Initial load
  setRole(currentRole, false);
});
