<?php
require_once 'config.php';

$userId       = $_SESSION['user_id']    ?? 2;
$userName     = $_SESSION['full_name']  ?? 'Fahim';
$userRole     = $_SESSION['role']       ?? 'Student';
$userEmail    = $_SESSION['email']      ?? 'fahim@uiu.ac.bd';
$studentId    = $_SESSION['student_id'] ?? '011 221 045';
$department   = $_SESSION['department'] ?? 'Computer Science & Engineering';
$avatarLetter = strtoupper(substr($userName, 0, 1));

$error = null;

// Values to show in the summary card (support both GET initial load and POST resubmission)
$name        = $_POST['venue_name']     ?? ($_GET['name']     ?? null);
$location    = $_POST['venue_location'] ?? ($_GET['location'] ?? null);
$capacity    = $_POST['venue_capacity'] ?? ($_GET['capacity'] ?? '--');
$image       = $_POST['venue_image']    ?? ($_GET['image']    ?? 'https://placehold.co/500x300?text=Select+a+Venue');
$status      = $_POST['venue_status']   ?? ($_GET['status']   ?? 'Available');
$fromPage    = $_POST['from']           ?? ($_GET['from']     ?? 'venue-list.php');

$bookingDate = $_POST['booking_date'] ?? date('Y-m-d', strtotime('+1 day'));
$bookingTime = $_POST['booking_time'] ?? '09:00 AM – 11:00 AM';
$purpose     = $_POST['purpose']      ?? '';

// If accessed directly with no venue selected, default to the first venue so the page displays properly
if ($name === null || trim($name) === '') {
    if ($pdo) {
        try {
            $firstStmt = $pdo->query("SELECT * FROM venues ORDER BY name LIMIT 1");
            $first = $firstStmt->fetch();
            if ($first) {
                $name     = $first['name'];
                $location = $first['location'];
                $capacity = $first['capacity'] . ' Seats';
                $image    = $first['thumbnail'];
                $status   = $first['status'];
            }
        } catch (Throwable $e) {}
    }

    if ($name === null) {
        $name     = 'Electrical Lab 4';
        $location = 'Main Campus, North Wing';
        $capacity = '40 Seats';
        $image    = 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=400&h=280&fit=crop';
        $status   = 'Available';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');

    if ($pdo === null) {
        $error = "Database is not connected. Please ensure MySQL is started in XAMPP.";
    } elseif (empty($name) || empty($bookingDate) || empty($bookingTime) || empty($purpose)) {
        $error = "Please fill in all required fields (date, time range, and purpose).";
    } elseif ($bookingDate < date('Y-m-d')) {
        $error = "Reservation date cannot be in the past.";
    } elseif ($description === '') {
        $error = "Please write an event description before submitting.";
    } else {
        $attachment = null;

        // Process optional uploaded document with type and size validation
        if (isset($_FILES['supporting_document']) && $_FILES['supporting_document']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['supporting_document'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

            if ($file['size'] > 5 * 1024 * 1024) {
                $error = "File is too large. Maximum allowed size is 5MB.";
            } elseif (!in_array($ext, $allowedExts, true)) {
                $error = "Invalid file type. Allowed formats: PDF, JPG, PNG, DOC, DOCX.";
            } else {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $safeName)) {
                    $attachment = $safeName;
                }
            }
        }

        // Check for time slot conflict on the same venue
        if ($error === null) {
            try {
                $check = $pdo->prepare(
                    "SELECT COUNT(*) FROM requests 
                     WHERE venue_name = ? AND booking_date = ? AND booking_time = ? AND status != 'Rejected'"
                );
                $check->execute([$name, $bookingDate, $bookingTime]);
                if ($check->fetchColumn() > 0) {
                    $error = "This venue is already booked for this date and time slot. Please choose another time.";
                }
            } catch (Throwable $e) {
                $error = "Database query error: " . $e->getMessage();
            }
        }

        if ($error === null) {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO requests (user_id, venue_name, venue_location, booking_date, booking_time, purpose, description, attachment, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')"
                );
                $stmt->execute([$userId, $name, $location, $bookingDate, $bookingTime, $purpose, $description, $attachment]);

                // Automatically create a notification for the user
                try {
                    $notifMsg = "Your booking request for {$name} on {$bookingDate} ({$bookingTime}) has been submitted and is pending review.";
                    $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, time_ago, is_read) VALUES (?, 'Booking Request Submitted', ?, 'approval', 'Just now', 0)");
                    $notifStmt->execute([$userId, $notifMsg]);
                } catch (Throwable $ex) {
                    // Non-critical, continue
                }

                header('Location: requests.php');
                exit;
            } catch (Throwable $e) {
                $error = "Could not save booking request: " . $e->getMessage();
            }
        }
    }
}

$badgeClass = ($status === 'Booked') ? 'badge-booked' : 'badge-available';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Request Booking - VenueVibe</title>
  
  <!-- Google Font: Geist -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <!-- Stylesheet -->
  <link rel="stylesheet" href="request-booking.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

  <!-- ================= APP CONTAINER ================= -->
  <div class="app-container">

    <!-- ================= 1. SIDEBAR ================= -->
    <aside class="sidebar">
      <div class="sidebar-top">
        <!-- Brand Wordmark -->
        <a href="venue-list.php" class="brand">
          <span class="brand-text">VenueVibe</span>
        </a>

        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
          <a href="user.php" class="nav-link">
            <svg class="nav-icon" viewBox="0 0 24 24">
              <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
              <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
              <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
              <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
            </svg>
            <span>Dashboard</span>
          </a>

          <a href="venue-list.php" class="nav-link active">
            <svg class="nav-icon" viewBox="0 0 24 24">
              <rect x="4" y="2" width="16" height="20" rx="2"></rect>
              <line x1="9" y1="6" x2="9.01" y2="6"></line>
              <line x1="15" y1="6" x2="15.01" y2="6"></line>
              <line x1="9" y1="10" x2="9.01" y2="10"></line>
              <line x1="15" y1="10" x2="15.01" y2="10"></line>
              <line x1="9" y1="14" x2="9.01" y2="14"></line>
            </svg>
            <span>Venues</span>
          </a>

          <a href="requests.php" class="nav-link">
            <svg class="nav-icon" viewBox="0 0 24 24">
              <rect x="3" y="4" width="18" height="18" rx="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>My Bookings</span>
          </a>

          <a href="messages.php" class="nav-link" id="navMessages">
            <svg class="nav-icon" viewBox="0 0 24 24">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>Messages</span>
          </a>

          <a href="notifications.php" class="nav-link" id="sidebarNotificationsLink">
            <svg class="nav-icon" viewBox="0 0 24 24">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span>Notifications</span>
          </a>
        </nav>
      </div>
    </aside>

    <!-- ================= MAIN CONTENT WRAPPER ================= -->
    <main class="main-wrapper">

      <!-- ================= 2. ELEVATED MODERN NAVBAR ================= -->
      <header class="top-header">
        <div class="header-left">
          <h1 class="nav-page-title">Request Booking</h1>
        </div>

        <div class="header-right">
          <!-- User Profile Capsule Button -->
          <button class="user-capsule-btn" id="userBtn" title="Account settings & profile" type="button">
            <div class="avatar-wrapper">
              <div class="avatar-letter"><?= htmlspecialchars($avatarLetter) ?></div>
              <span class="online-indicator"></span>
            </div>
            <div class="user-capsule-text">
              <span class="capsule-name"><?= htmlspecialchars($userName) ?></span>
              <span class="capsule-role"><?= htmlspecialchars(ucfirst($userRole)) ?></span>
            </div>
            <svg class="capsule-chevron" viewBox="0 0 24 24">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
        </div>
      </header>

      <!-- ================= PAGE CONTENT AREA ================= -->
      <div class="dashboard-content">

        <?php if ($error): ?>
          <div style="background-color: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.25); color: var(--danger-hover); padding: 12px 18px; border-radius: var(--radius-xl); font-size: var(--text-sm);">
            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <div class="booking-wrapper">

          <div class="form-section">
            <form method="post" action="request-booking.php" enctype="multipart/form-data">
              <!-- Hidden fields to preserve state on submission or error -->
              <input type="hidden" name="venue_name" value="<?= htmlspecialchars($name) ?>">
              <input type="hidden" name="venue_location" value="<?= htmlspecialchars($location) ?>">
              <input type="hidden" name="venue_capacity" value="<?= htmlspecialchars($capacity) ?>">
              <input type="hidden" name="venue_image" value="<?= htmlspecialchars($image) ?>">
              <input type="hidden" name="venue_status" value="<?= htmlspecialchars($status) ?>">
              <input type="hidden" name="from" value="<?= htmlspecialchars($fromPage) ?>">

              <div class="panel">
                <h2 style="font-size: 1.25rem; margin-bottom: 4px;">Reservation Details</h2>
                <div class="about-text" style="margin-bottom: 24px;">Complete the reservation details below. Campus management reviews requests within 24 hours.</div>

                <div class="form-group">
                  <label for="bookingDate">Reservation Date</label>
                  <input type="date" name="booking_date" id="bookingDate" value="<?= htmlspecialchars($bookingDate) ?>" min="<?= date('Y-m-d') ?>" onchange="updateSummary()" required>
                </div>

                <div class="form-group">
                  <label for="bookingTime">Time Range</label>
                  <select name="booking_time" id="bookingTime" onchange="updateSummary()" required>
                    <option value="" disabled <?= empty($bookingTime) ? 'selected' : '' ?>>Select a time range</option>
                    <option value="09:00 AM – 11:00 AM" <?= ($bookingTime === '09:00 AM – 11:00 AM') ? 'selected' : '' ?>>09:00 AM – 11:00 AM</option>
                    <option value="11:00 AM – 01:00 PM" <?= ($bookingTime === '11:00 AM – 01:00 PM') ? 'selected' : '' ?>>11:00 AM – 01:00 PM</option>
                    <option value="02:00 PM – 04:00 PM" <?= ($bookingTime === '02:00 PM – 04:00 PM') ? 'selected' : '' ?>>02:00 PM – 04:00 PM</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="purposeSelect">Purpose of Booking</label>
                  <select name="purpose" id="purposeSelect" required>
                    <option value="" disabled <?= empty($purpose) ? 'selected' : '' ?>>Select a purpose</option>
                    <option value="Academic Lecture" <?= ($purpose === 'Academic Lecture') ? 'selected' : '' ?>>Academic Lecture</option>
                    <option value="Seminar / Workshop" <?= ($purpose === 'Seminar / Workshop') ? 'selected' : '' ?>>Seminar / Workshop</option>
                    <option value="Cultural Event" <?= ($purpose === 'Cultural Event') ? 'selected' : '' ?>>Cultural Event</option>
                    <option value="Club Activity" <?= ($purpose === 'Club Activity') ? 'selected' : '' ?>>Club Activity</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="descriptionField">Event Description</label>
                  <textarea name="description" id="descriptionField" rows="4" placeholder="Describe the activities, expected attendance, and any technical setup required..." required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                </div>

                <div class="form-group">
                  <label>Supporting Documents (Optional)</label>
                  <div class="upload-box" onclick="document.getElementById('fileInput').click()">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 24px; color: var(--primary); margin-bottom: 8px;"></i>
                    <br>
                    <strong>Click to choose a file to upload</strong>
                    <br>
                    <span>PDF, JPG, PNG, DOC (Max 5MB)</span>
                    <input type="file" name="supporting_document" id="fileInput" onchange="showFileName()" style="display: none;">
                    <div id="fileName" style="margin-top: 10px; font-weight: 600; color: var(--primary);"></div>
                  </div>
                </div>

                <div class="form-buttons">
                  <button type="button" class="cancel-btn" onclick="cancelForm()">Reset</button>
                  <button type="submit" class="submit-btn">
                    <i class="fa-solid fa-paper-plane"></i> Submit Request
                  </button>
                </div>
              </div>
            </form>
          </div>

          <aside class="summary-section">
            <div class="summary-card">
              <span class="<?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
              <img class="summary-image" id="summaryImage" src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($name) ?>">

              <div class="summary-body">
                <div class="venue-title"><?= htmlspecialchars($name) ?></div>
                <div class="venue-meta"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($location) ?></div>
                <br>
                <div class="summary-row">
                  <span>Capacity</span>
                  <strong><?= htmlspecialchars($capacity) ?></strong>
                </div>
              </div>

              <div class="booking-summary-box">
                <div class="summary-row">
                  <span>Date</span>
                  <strong id="summaryDate"><?= htmlspecialchars($bookingDate) ?></strong>
                </div>
                <div class="summary-row">
                  <span>Time</span>
                  <strong id="summaryTime"><?= htmlspecialchars($bookingTime) ?></strong>
                </div>
              </div>

              <div class="help-box">
                <strong><i class="fa-solid fa-circle-question"></i> Need assistance?</strong>
                <p style="margin: 6px 0 10px;">Our venue support desk is available Mon–Fri, 9:00 AM – 5:00 PM.</p>
                <a href="mailto:venuesupport@campus.edu" style="color: var(--primary); font-weight: 600;">Contact Support &rarr;</a>
              </div>
            </div>
          </aside>

        </div>

      </div>

      <!-- ================= 3. SIMPLIFIED MODERN FOOTER ================= -->
      <footer class="app-footer">
        <div class="footer-simple-content">
          <div class="footer-left">
            <div class="footer-brand-title">
              <span class="brand-text">VenueVibe</span>
              <span class="footer-pill-tag">Campus Solutions</span>
            </div>
            <p class="footer-simple-desc">Smart venue reservation and campus facility management portal.</p>
          </div>

          <div class="footer-right">
            <nav class="footer-simple-links">
              <a href="mailto:venue@uiu.ac.bd" class="footer-email-link" title="Contact Email" style="display: inline-flex; align-items: center; gap: 5px; color: var(--primary, #6366f1); font-weight: 500;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <span>venue@uiu.ac.bd</span>
              </a>
              <a href="#">Guidelines</a>
              <a href="#">Campus Directory</a>
              <a href="#">Helpdesk</a>
              <a href="#">Terms & Privacy</a>
            </nav>
            <div class="status-indicator-pill">
              <span class="live-dot"></span>
              <span>All Systems Operational</span>
            </div>
          </div>
        </div>

        <div class="footer-bottom-bar">
          <p>© <span id="year">2026</span> VenueVibe Campus Solutions. All rights reserved.</p>
        </div>
      </footer>

    </main>

  </div><!-- /app-container -->

  <!-- ================= USER PROFILE MODAL ================= -->
  <div class="profile-modal-overlay" id="profileModalOverlay">
    <div class="profile-card" id="profileCard">
      <button class="modal-close-btn" id="closeProfileModal" title="Close Profile" type="button">
        <svg class="icon-close" viewBox="0 0 24 24">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>

      <div class="profile-header">
        <div class="profile-avatar-large">
          <div class="avatar-letter-large"><?= htmlspecialchars($avatarLetter) ?></div>
          <span class="online-dot-large"></span>
        </div>
        <h3 class="profile-name"><?= htmlspecialchars($userName) ?></h3>
        <span class="profile-role"><?= htmlspecialchars(ucfirst($userRole)) ?> / <?= htmlspecialchars($department) ?></span>
      </div>

      <div class="profile-details-list">
        <div class="detail-row">
          <span class="detail-label">Student ID</span>
          <span class="detail-value"><?= htmlspecialchars($studentId) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Email</span>
          <span class="detail-value"><?= htmlspecialchars($userEmail) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Department</span>
          <span class="detail-value"><?= htmlspecialchars($department) ?></span>
        </div>
      </div>

      <div class="profile-actions">
        <button class="btn-profile-logout" id="profileLogoutBtn" onclick="window.location.href='logout.php'" type="button">Logout</button>
      </div>
    </div>
  </div>

  <script src="common.js"></script>
  <script src="request-booking.js"></script>
</body>
</html>
