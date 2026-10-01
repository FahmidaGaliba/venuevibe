document.addEventListener("DOMContentLoaded", function () {
    const passwordInput = document.getElementById("passwordInput");
    const togglePasswordBtn = document.getElementById("togglePassword");
    const alertBox = document.getElementById("alertBox");
    const signupForm = document.getElementById("signupForm");

    function showAlert(message, type = "error") {
        if (alertBox) {
            alertBox.className = `alert-box alert-${type}`;
            const iconClass = type === "success" ? "fa-circle-check" : "fa-circle-exclamation";
            alertBox.innerHTML = `<i class="fa-solid ${iconClass}"></i> <span>${message}</span>`;
            alertBox.style.display = "flex";
        }
    }

    function hideAlert() {
        if (alertBox) {
            alertBox.style.display = "none";
        }
    }

    // Toggle password visibility
    if (passwordInput && togglePasswordBtn) {
        togglePasswordBtn.addEventListener("click", function () {
            const isHidden = passwordInput.type === "password";
            passwordInput.type = isHidden ? "text" : "password";
            const icon = togglePasswordBtn.querySelector("i");
            if (icon) {
                icon.classList.toggle("fa-eye", !isHidden);
                icon.classList.toggle("fa-eye-slash", isHidden);
            }
        });
    }

    // Display errors passed in the URL from signup.php
    const params = new URLSearchParams(window.location.search);
    const error = params.get("error");
    if (error) {
        showAlert(decodeURIComponent(error));
    }

    // Client-side validation & clear alert on typing
    if (signupForm) {
        signupForm.querySelectorAll("input").forEach((input) => {
            input.addEventListener("input", hideAlert);
        });

        signupForm.addEventListener("submit", function (e) {
            if (passwordInput && passwordInput.value.length < 8) {
                e.preventDefault();
                showAlert("Password must be at least 8 characters long.");
                passwordInput.focus();
                return;
            }
        });
    }
});
