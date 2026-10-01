/* ============================================================
   VenueVibe - request-booking.js
   Client-side Logic for Booking Request Submission & Summary Sync
   ============================================================ */

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
    window.location.href = "requests.php";
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

document.addEventListener("DOMContentLoaded", function() {
    var dateInput = document.getElementById("bookingDate");
    var timeSelect = document.getElementById("bookingTime");

    if (dateInput) dateInput.addEventListener("change", updateSummary);
    if (timeSelect) timeSelect.addEventListener("change", updateSummary);
});
