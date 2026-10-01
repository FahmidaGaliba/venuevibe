/* ==========================================================================
   VenueVibe - common.js
   Unified Client-side Logic: Modals, Booking, Catalog Search & Request Tracking
   ========================================================================== */

/* ==========================================================================
   1. Shared Global Utilities & UI Handlers
   ========================================================================== */

function goToBooking(name, location, capacity, image, status) {
    var currentPage = window.location.pathname.split("/").pop();
    var isPhp = window.location.pathname.indexOf(".php") > -1;
    var targetPage = isPhp ? "request-booking.php" : "request-booking.html";
    var fromParam = (currentPage || "") + (window.location.search || "");

    var url = targetPage;
    url += "?name=" + encodeURIComponent(name || "");
    url += "&location=" + encodeURIComponent(location || "");
    url += "&capacity=" + encodeURIComponent(capacity || "");
    url += "&image=" + encodeURIComponent(image || "");
    url += "&status=" + encodeURIComponent(status || "");
    url += "&from=" + encodeURIComponent(fromParam);

    window.location.href = url;
}

function showAllReviews() {
    alert("More reviews will be added soon!");
}

function initCommonUI() {
    // 1. Automatically set current year in the footer
    var yearSpan = document.getElementById("year");
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }

    // 2. Profile Modal Open / Close Logic
    var userBtn = document.getElementById("userBtn");
    var profileModalOverlay = document.getElementById("profileModalOverlay");
    var closeProfileModal = document.getElementById("closeProfileModal");
    var profileLogoutBtn = document.getElementById("profileLogoutBtn");

    function openProfile() {
        if (profileModalOverlay) {
            profileModalOverlay.classList.add("open");
        }
    }

    function closeProfile() {
        if (profileModalOverlay) {
            profileModalOverlay.classList.remove("open");
        }
    }

    if (userBtn) {
        userBtn.addEventListener("click", openProfile);
    }

    if (closeProfileModal) {
        closeProfileModal.addEventListener("click", closeProfile);
    }

    // Close modal when clicking the dark backdrop outside
    if (profileModalOverlay) {
        profileModalOverlay.addEventListener("click", function(e) {
            if (e.target === profileModalOverlay) {
                closeProfile();
            }
        });
    }

    // Close modal on Escape key press
    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") {
            closeProfile();
        }
    });

    // Logout button handler
    if (profileLogoutBtn) {
        profileLogoutBtn.addEventListener("click", function() {
            window.location.href = "logout.php";
        });
    }

    // Ensure messages and notifications links point to live php pages
    var navMessages = document.getElementById("navMessages");
    if (navMessages && (!navMessages.getAttribute("href") || navMessages.getAttribute("href") === "#" || navMessages.getAttribute("href") === "messages.html")) {
        navMessages.setAttribute("href", "messages.php");
    }

    var sidebarNotifications = document.getElementById("sidebarNotificationsLink");
    if (sidebarNotifications && (!sidebarNotifications.getAttribute("href") || sidebarNotifications.getAttribute("href") === "#" || sidebarNotifications.getAttribute("href") === "notifications.html")) {
        sidebarNotifications.setAttribute("href", "notifications.php");
    }
}

// Bind UI handlers on DOM ready
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initCommonUI);
} else {
    initCommonUI();
}

/* ==========================================================================
   2. Venue Catalog & Search Logic (Merged from venue-list.js)
   ========================================================================== */

function renderVenues() {
    var grid = document.getElementById("venueGrid");
    if (!grid || typeof VENUES === "undefined") return;

    var html = "";
    for (var i = 0; i < VENUES.length; i++) {
        var v = VENUES[i];
        var badgeClass = (v.status === "Booked") ? "badge-booked" : "badge-available";

        html += "<div class='venue-card' onclick=\"goToDetails('" + v.id + "')\">";
        html += "  <div class='venue-image-wrap'>";
        html += "    <span class='" + badgeClass + "'>" + v.status + "</span>";
        html += "    <img class='venue-image' src='" + v.thumbnail + "' alt='" + v.name + "'>";
        html += "  </div>";
        html += "  <div class='card-body'>";
        html += "    <div class='card-title-row'>";
        html += "      <div class='venue-title'>" + v.name + "</div>";
        html += "      <div class='venue-type'>" + v.type + "</div>";
        html += "    </div>";
        html += "    <div class='venue-meta'><i class=\"fa-solid fa-location-dot\"></i> " + v.location + "</div>";
        html += "    <div class='venue-meta'><i class=\"fa-solid fa-users\"></i> Capacity: " + v.capacity + " People</div>";
        html += "    <button type='button' class='view-btn'>View Details</button>";
        html += "  </div>";
        html += "</div>";
    }

    grid.innerHTML = html;
}

function searchVenue() {
    var inputElem = document.getElementById("searchInput");
    if (!inputElem) return;
    var input = inputElem.value.toLowerCase().trim();
    var cards = document.getElementsByClassName("venue-card");
    for (var i = 0; i < cards.length; i++) {
        var text = cards[i].innerText.toLowerCase();
        cards[i].style.display = (text.indexOf(input) > -1) ? "" : "none";
    }
}

function filterType(type, element) {
    var pills = document.querySelectorAll(".filter-pill");
    pills.forEach(function(pill) { pill.classList.remove("active"); });
    if (element) element.classList.add("active");

    var filter = type.toLowerCase().trim();
    var cards = document.getElementsByClassName("venue-card");
    for (var i = 0; i < cards.length; i++) {
        if (filter === "all") {
            cards[i].style.display = "";
        } else {
            var typeElem = cards[i].querySelector(".venue-type");
            var venueType = typeElem ? typeElem.innerText.toLowerCase() : "";
            cards[i].style.display = (venueType.indexOf(filter) > -1) ? "" : "none";
        }
    }
}

function goToDetails(id) {
    var isPhp = window.location.pathname.indexOf(".php") > -1;
    var target = isPhp ? "venue-details.php" : "venue-details.html";
    window.location.href = target + "?venue=" + encodeURIComponent(id);
}

/* ==========================================================================
   3. Venue Details Loader Logic (Merged from venue-details.js)
   ========================================================================== */

function loadVenueDetails() {
    var params = new URLSearchParams(window.location.search);
    var venueId = params.get("venue");

    // Graceful fallback to first venue if opened directly without a parameter
    if (!venueId && typeof VENUES !== "undefined" && VENUES.length > 0) {
        venueId = VENUES[0].id;
    }

    var venue = (venueId && typeof getVenueById === "function") ? getVenueById(venueId) : null;

    if (venue == null) {
        // If neither selected nor mock data found, redirect to list
        window.location.href = "venue-list.html";
        return;
    }

    document.title = venue.name + " - VenueVibe";

    var heroImg = document.getElementById("heroImage");
    if (heroImg) {
        heroImg.src = venue.image;
        heroImg.alt = venue.name;
    }

    var nameElem = document.getElementById("venueName");
    if (nameElem) nameElem.innerText = venue.name;
    var locElem = document.getElementById("venueLocation");
    if (locElem) locElem.innerText = venue.location;
    var ratingElem = document.getElementById("venueRating");
    if (ratingElem) ratingElem.innerText = (typeof venue.rating === "number") ? venue.rating.toFixed(1) : venue.rating;

    var badge = document.getElementById("statusBadge");
    if (badge) {
        badge.innerText = venue.status;
        badge.className = (venue.status === "Booked") ? "badge-booked" : "badge-available";
    }

    var capElem = document.getElementById("statCapacity");
    if (capElem) capElem.innerText = venue.capacity + " Seats";
    var infraElem = document.getElementById("statInfrastructure");
    if (infraElem) infraElem.innerText = venue.infrastructure || "Standard";
    var statRateElem = document.getElementById("statRating");
    if (statRateElem) statRateElem.innerText = ((typeof venue.rating === "number") ? venue.rating.toFixed(1) : venue.rating) + " / 5.0";

    var equipmentHtml = "";
    var equipment = venue.equipment || [];
    for (var i = 0; i < equipment.length; i++) {
        equipmentHtml += "<span class='equipment-tag'><i class=\"fa-solid fa-check\"></i> " + equipment[i] + "</span>";
    }
    var eqContainer = document.getElementById("equipmentTags");
    if (eqContainer) eqContainer.innerHTML = equipmentHtml || "<span class='about-text'>Standard venue facilities provided.</span>";

    var aboutElem = document.getElementById("aboutText");
    if (aboutElem) aboutElem.innerText = venue.about || "";

    var scheduleHtml = "";
    var schedule = venue.schedule || [];
    for (var j = 0; j < schedule.length; j++) {
        var item = schedule[j];
        var statusClass = (item.status === "confirmed") ? "status-confirmed" : "status-tentative";
        var statusLabel = (item.status === "confirmed") ? "Confirmed" : "Tentative";

        scheduleHtml += "<div class='schedule-box'>";
        scheduleHtml += "<div><h4>" + item.date + "</h4> " + item.title + "<br><span class='venue-meta'>" + item.time + " · Host: " + item.host + "</span></div>";
        scheduleHtml += "<span class='" + statusClass + "'>" + statusLabel + "</span>";
        scheduleHtml += "</div>";
    }
    var schedContainer = document.getElementById("scheduleList");
    if (schedContainer) {
        schedContainer.innerHTML = scheduleHtml || "<div class='about-text'>No upcoming events scheduled for this venue.</div>";
    }

    var r = venue.review;
    var reviewContainer = document.getElementById("reviewBox");
    if (reviewContainer) {
        if (r) {
            var reviewHtml = "<div class='review-box'>";
            reviewHtml += "<div class='review-circle'>" + r.initials + "</div>";
            reviewHtml += "<div class='review-text-content'>";
            reviewHtml += "<span class='review-author'>" + r.name + "</span> <span class='review-time'>· " + r.time + "</span>";
            reviewHtml += "<p style='margin: 8px 0 0;'>\"" + r.text + "\"</p>";
            reviewHtml += "</div></div>";
            reviewContainer.innerHTML = reviewHtml;
        } else {
            reviewContainer.innerHTML = "<div class='about-text'>No reviews recorded yet for this venue.</div>";
        }
    }

    var bookBtn = document.getElementById("bookBtn");
    if (bookBtn) {
        bookBtn.onclick = function () {
            goToBooking(venue.name, venue.location, venue.capacity + " Seats", venue.thumbnail, venue.status);
        };
    }
}

/* ==========================================================================
   4. Request Booking Form Logic (Merged from request-booking.js)
   ========================================================================== */

function loadVenueInfo() {
    var params = new URLSearchParams(window.location.search);

    var name     = params.get("name");
    var location = params.get("location");
    var capacity = params.get("capacity");
    var image    = params.get("image");
    var status   = params.get("status");

    // Graceful fallback if opened directly without query parameters
    if (!name && typeof VENUES !== "undefined" && VENUES.length > 0) {
        name     = VENUES[0].name;
        location = VENUES[0].location;
        capacity = VENUES[0].capacity + " Seats";
        image    = VENUES[0].thumbnail;
        status   = VENUES[0].status;
    }

    var titleElem = document.getElementById("summaryVenueName");
    if (titleElem && name) titleElem.innerText = name;

    var locElem = document.getElementById("summaryLocation");
    if (locElem && location) locElem.innerText = location;

    var capElem = document.getElementById("summaryCapacity");
    if (capElem && capacity) capElem.innerText = capacity;

    var imgElem = document.getElementById("summaryImage");
    if (imgElem && image) imgElem.src = image;

    if (status) {
        var badge = document.getElementById("summaryBadge");
        if (badge) {
            badge.innerText = status;
            badge.className = (status === "Booked") ? "badge-booked" : "badge-available";
        }
    }

    var dateInput = document.getElementById("bookingDate");
    if (dateInput) {
        var tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        var tomorrowStr = tomorrow.toISOString().split("T")[0];
        if (!dateInput.value) {
            dateInput.value = tomorrowStr;
        }
        dateInput.min = new Date().toISOString().split("T")[0];
        updateSummary();
    }
}

function updateSummary() {
    var dateInput = document.getElementById("bookingDate");
    var timeSelect = document.getElementById("bookingTime");
    var sumDate = document.getElementById("summaryDate");
    var sumTime = document.getElementById("summaryTime");

    if (sumDate && dateInput && dateInput.value) {
        sumDate.innerText = dateInput.value;
    }
    if (sumTime && timeSelect && timeSelect.value) {
        sumTime.innerText = timeSelect.value;
    }
}

function showFileName() {
    var fileInput = document.getElementById("fileInput");
    var display = document.getElementById("fileName");
    if (!display) return;
    if (fileInput && fileInput.files && fileInput.files.length > 0) {
        var file = fileInput.files[0];
        if (file.size > 5 * 1024 * 1024) {
            alert("File is too large. Maximum allowed size is 5MB.");
            fileInput.value = "";
            display.innerText = "";
            return;
        }
        display.innerText = "Selected: " + file.name;
    } else {
        display.innerText = "";
    }
}

function validateForm() {
    var nameElem = document.getElementById("summaryVenueName");
    var name = nameElem ? nameElem.innerText : "";
    var locElem = document.getElementById("summaryLocation");
    var location = locElem ? locElem.innerText : "";
    var dateInput = document.getElementById("bookingDate");
    var date = dateInput ? dateInput.value : "";
    var timeSelect = document.getElementById("bookingTime");
    var time = timeSelect ? timeSelect.value : "";
    var purposeElem = document.getElementById("purpose") || document.getElementById("purposeSelect");
    var purpose = purposeElem ? purposeElem.value : "";
    var descElem = document.getElementById("description") || document.getElementById("descriptionField");
    var description = descElem ? descElem.value.trim() : "";

    if (!date || !time || !purpose) {
        alert("Please fill in reservation date, time range, and purpose.");
        return;
    }

    if (description === "") {
        alert("Please write an event description before submitting.");
        return;
    }

    var savedRequests = localStorage.getItem("venueRequests");
    var requestsList = savedRequests ? JSON.parse(savedRequests) : [];

    // Double-booking conflict check
    for (var i = 0; i < requestsList.length; i++) {
        var r = requestsList[i];
        if (r.name === name && r.date === date && r.time === time && r.status !== "Rejected") {
            alert("This venue is already booked for this date and time slot. Please choose another time.");
            return;
        }
    }

    var newRequest = {
        id: Date.now(),
        name: name,
        location: location,
        date: date,
        time: time,
        purpose: purpose,
        status: "Pending"
    };

    requestsList.push(newRequest);
    localStorage.setItem("venueRequests", JSON.stringify(requestsList));

    alert("Your booking request has been submitted!");
    var isPhp = window.location.pathname.indexOf(".php") > -1;
    window.location.href = isPhp ? "requests.php" : "requests.html";
}

function cancelForm() {
    if (!confirm("Reset the form fields?")) return;
    var desc = document.getElementById("description") || document.getElementById("descriptionField");
    if (desc) desc.value = "";
    var fileInput = document.getElementById("fileInput");
    if (fileInput) fileInput.value = "";
    var fileName = document.getElementById("fileName");
    if (fileName) fileName.innerText = "";
    var purpose = document.getElementById("purpose") || document.getElementById("purposeSelect");
    if (purpose) purpose.selectedIndex = 0;
}

/* ==========================================================================
   5. Booking Requests Tracking Logic (Merged from requests.js)
   ========================================================================== */

var currentFilter = "all";

function loadRequests() {
    var container = document.getElementById("requestsList");
    if (!container) return;

    var requestsList = getSavedRequests();

    var needsSave = false;
    for (var k = 0; k < requestsList.length; k++) {
        if (requestsList[k].id == null) {
            requestsList[k].id = Date.now() + k;
            needsSave = true;
        }
    }
    if (needsSave) {
        localStorage.setItem("venueRequests", JSON.stringify(requestsList));
    }

    if (requestsList.length === 0) {
        container.innerHTML = "<div class='about-text'>You have not submitted any booking requests yet. Browse venues to book your first space!</div>";
        updateCounts([]);
        return;
    }

    var html = "";
    for (var i = 0; i < requestsList.length; i++) {
        var r = requestsList[i];
        var status = r.status || "Pending";

        html += "<div class='request-row' data-status='" + status + "'>";
        html += "  <div>";
        html += "    <div class='request-venue'><strong>" + r.name + "</strong> &bull; " + r.location + "</div>";
        html += "    <div class='request-meta'><i class='fa-regular fa-calendar'></i> " + r.date + " &bull; <i class='fa-regular fa-clock'></i> " + r.time + " &bull; <i class='fa-solid fa-tag'></i> " + r.purpose + "</div>";
        html += "  </div>";
        html += "  <div class='request-action'>";
        html += "    " + statusBadgeHtml(status);

        if (status === "Pending") {
            html += "    <button type='button' class='row-cancel-btn' onclick=\"cancelRequest(" + r.id + ")\">Cancel</button>";
        }

        html += "  </div>";
        html += "</div>";
    }

    container.innerHTML = html;
    updateCounts(requestsList);
    filterRequests(currentFilter);
}

function statusBadgeHtml(status) {
    if (status === "Approved") {
        return "<span class='status-approved'><i class='fa-solid fa-check'></i> Approved</span>";
    }
    if (status === "Rejected") {
        return "<span class='status-rejected'><i class='fa-solid fa-xmark'></i> Rejected</span>";
    }
    return "<span class='status-pending'><i class='fa-solid fa-hourglass-half'></i> Pending</span>";
}

function updateCounts(requestsList) {
    var pending = 0, approved = 0, rejected = 0;

    for (var i = 0; i < requestsList.length; i++) {
        var status = requestsList[i].status || "Pending";
        if (status === "Approved") approved++;
        else if (status === "Rejected") rejected++;
        else pending++;
    }

    var countAll = document.getElementById("countAll");
    var countPen = document.getElementById("countPending");
    var countApp = document.getElementById("countApproved");
    var countRej = document.getElementById("countRejected");

    if (countAll) countAll.innerText = requestsList.length;
    if (countPen) countPen.innerText = pending;
    if (countApp) countApp.innerText = approved;
    if (countRej) countRej.innerText = rejected;
}

function filterRequests(status) {
    currentFilter = status;

    var rows = document.getElementsByClassName("request-row");
    for (var i = 0; i < rows.length; i++) {
        var rowStatus = rows[i].getAttribute("data-status");
        rows[i].style.display = (status === "all" || rowStatus === status) ? "" : "none";
    }

    var tabs = document.getElementsByClassName("filter-tab");
    for (var j = 0; j < tabs.length; j++) {
        if (tabs[j].getAttribute("data-filter") === status) {
            tabs[j].classList.add("active");
        } else {
            tabs[j].classList.remove("active");
        }
    }
}

function cancelRequest(id) {
    if (!confirm("Are you sure you want to cancel this booking request?")) {
        return;
    }

    var isPhp = window.location.pathname.endsWith(".php");
    if (isPhp) {
        window.location.href = "requests.php?cancel=" + encodeURIComponent(id);
        return;
    }

    var requestsList = getSavedRequests();
    var updatedList = [];

    for (var i = 0; i < requestsList.length; i++) {
        if (requestsList[i].id != id) {
            updatedList.push(requestsList[i]);
        }
    }

    localStorage.setItem("venueRequests", JSON.stringify(updatedList));
    loadRequests();
}

function getSavedRequests() {
    var saved = localStorage.getItem("venueRequests");
    return saved ? JSON.parse(saved) : [];
}
