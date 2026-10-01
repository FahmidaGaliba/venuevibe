/* ============================================================
   VenueVibe - venue-details.js
   Client Logic for Venue Details, Schedule & Booking Redirection
   ============================================================ */

function loadVenueDetails() {
    var params = new URLSearchParams(window.location.search);
    var venueId = params.get("venue");

    // Graceful fallback to first venue if opened directly without a parameter
    if (!venueId && typeof VENUES !== "undefined" && VENUES.length > 0) {
        venueId = VENUES[0].id;
    }

    var venue = (venueId && typeof getVenueById === "function") ? getVenueById(venueId) : null;

    if (venue == null) {
        window.location.href = "venue-list.php";
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

function goToBooking(name, location, capacity, image, status) {
    var isPhp = window.location.pathname.indexOf(".php") > -1;
    var targetPage = isPhp ? "request-booking.php" : "request-booking.html";
    var fromParam = (window.location.pathname.split("/").pop() || "") + (window.location.search || "");

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
