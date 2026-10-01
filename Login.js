document.addEventListener("DOMContentLoaded", function () {
    const userBtn = document.getElementById("userBtn");
    const adminBtn = document.getElementById("adminBtn");
    const loginForm = document.getElementById("loginform");
    const title = document.getElementById("Title");
    const emailLabel = document.getElementById("emailLabel");
    const emailInput = document.getElementById("emailInput");
    const passwordInput = document.getElementById("passwordInput");
    const loginBtn = document.getElementById("loginBtn");
    const togglePasswordBtn = document.getElementById("togglePassword");
    const alertBox = document.getElementById("alertBox");

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

    // Toggle to User Login
    if (userBtn) {
        userBtn.addEventListener("click", function () {
            userBtn.classList.add("active");
            if (adminBtn) adminBtn.classList.remove("active");

            if (title) title.textContent = "User Login";
            if (emailLabel) emailLabel.textContent = "University Email";
            if (emailInput) emailInput.placeholder = "student@uiu.ac.bd";
            if (loginBtn) loginBtn.textContent = "Log In";
            hideAlert();
        });
    }

    // Toggle to Admin Login
    if (adminBtn) {
        adminBtn.addEventListener("click", function () {
            adminBtn.classList.add("active");
            if (userBtn) userBtn.classList.remove("active");

            if (title) title.textContent = "Admin Login";
            if (emailLabel) emailLabel.textContent = "Admin Email";
            if (emailInput) emailInput.placeholder = "faculty@uiu.ac.bd";
            if (loginBtn) loginBtn.textContent = "Admin Log In";
            hideAlert();
        });
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

    // Check for success message from signup redirect
    const params = new URLSearchParams(window.location.search);
    if (params.get("signup") === "success") {
        showAlert("Account created successfully! Please log in.", "success");
    }

    // Clear alert when user interacts with inputs
    if (loginForm) {
        loginForm.querySelectorAll("input").forEach((input) => {
            input.addEventListener("input", hideAlert);
        });

        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const email = emailInput ? emailInput.value.trim() : "";
            const password = passwordInput ? passwordInput.value : "";

            if (!email || !password) {
                showAlert("Please enter both email and password.");
                return;
            }

            const isAdmin = adminBtn && adminBtn.classList.contains("active");
            const formData = new FormData(loginForm);
            formData.append("login_type", isAdmin ? "admin" : "user");

            const originalText = loginBtn ? loginBtn.textContent : "Log In";
            if (loginBtn) {
                loginBtn.disabled = true;
                loginBtn.textContent = "Logging in...";
            }

            fetch("login.php", {
                method: "POST",
                body: formData,
            })
            .then((res) => {
                if (!res.ok) {
                    throw new Error("HTTP error " + res.status);
                }
                return res.json();
            })
            .then((data) => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    showAlert(data.message || "Login failed. Please try again.");
                }
            })
            .catch(() => {
                showAlert("Could not connect to the server. Please verify your backend server is running and try again.");
            })
            .finally(() => {
                if (loginBtn) {
                    loginBtn.disabled = false;
                    loginBtn.textContent = originalText;
                }
            });
        });
    }
});
