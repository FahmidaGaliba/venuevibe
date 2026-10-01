/* ============================================================
   VenueVibe - venue-list.js
   Interactive Client Logic for Venue Catalog & Search
   ============================================================ */

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

// Bind live search on input if present
document.addEventListener("DOMContentLoaded", function() {
    var searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener("keyup", searchVenue);
    }
});
