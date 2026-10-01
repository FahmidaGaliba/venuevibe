/* ============================================================
   VenueVibe - requests.js
   Client-side Logic for Booking Request Filtering & Cancellation
   ============================================================ */

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
        container.innerHTML = "<div class='about-text'>You have not submitted any booking requests yet. <a href='venue-list.php'>Browse venues</a> to book your first space!</div>";
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
        rows[i].style.display = (status === "all" || (rowStatus && rowStatus.toLowerCase() === status.toLowerCase())) ? "" : "none";
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

    var isPhp = window.location.pathname.indexOf(".php") > -1;
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

// Bind filter tabs on DOMContentLoaded
document.addEventListener("DOMContentLoaded", function() {
    var tabs = document.querySelectorAll(".filter-tab");
    tabs.forEach(function(tab) {
        tab.addEventListener("click", function() {
            var filter = this.getAttribute("data-filter");
            if (filter) filterRequests(filter);
        });
    });
});
