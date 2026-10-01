document.addEventListener("DOMContentLoaded", function () {
  // Update footer year dynamically
  const yearSpan = document.getElementById("year");
  if (yearSpan) {
    yearSpan.textContent = new Date().getFullYear();
  }

  // Navigation and CTA Button Actions
  const loginBtn = document.getElementById("loginbtn");
  if (loginBtn) {
    loginBtn.addEventListener("click", function () {
      window.location.href = "login.php";
    });
  }

  const signupBtn = document.getElementById("signupbtn");
  if (signupBtn) {
    signupBtn.addEventListener("click", function () {
      window.location.href = "signup.php";
    });
  }

  const bookBtn = document.getElementById("bookbtn");
  if (bookBtn) {
    bookBtn.addEventListener("click", function () {
      window.location.href = "venue-list.php";
    });
  }

  const learnMoreBtn = document.getElementById("learnMorebtn");
  if (learnMoreBtn) {
    learnMoreBtn.addEventListener("click", function () {
      const featureSection = document.getElementById("feature");
      if (featureSection) {
        featureSection.scrollIntoView({
          behavior: "smooth",
        });
      }
    });
  }
});