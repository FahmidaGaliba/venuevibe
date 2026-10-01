<?php
require_once 'config.php';

$userName     = $_SESSION['full_name']  ?? 'Fahim';
$userRole     = $_SESSION['role']       ?? 'Student';
$userEmail    = $_SESSION['email']      ?? 'fahim@uiu.ac.bd';
$studentId    = $_SESSION['student_id'] ?? '011 221 045';
$department   = $_SESSION['department'] ?? 'Computer Science & Engineering';
$avatarLetter = strtoupper(substr($userName, 0, 1));

$userId       = $_SESSION['user_id']    ?? 2;
$isAdmin      = !empty($_SESSION['is_admin']) || (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin');

// Handle admin approving or rejecting a request
if ($isAdmin && isset($_GET['action'], $_GET['id'])) {
    $reqId = (int)$_GET['id'];
    $act = $_GET['action'];
    if ($pdo && ($act === 'approve' || $act === 'reject')) {
        $newStatus = ($act === 'approve') ? 'Approved' : 'Rejected';
        try {
            $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $reqId]);

            // Add notification for the student
            $getReq = $pdo->prepare("SELECT user_id, venue_name, booking_date FROM requests WHERE id = ?");
            $getReq->execute([$reqId]);
            $targetReq = $getReq->fetch();
            if ($targetReq) {
                $targetUserId = $targetReq['user_id'];
                $venueName    = $targetReq['venue_name'];
                $bDate        = $targetReq['booking_date'];
                $notifType    = ($newStatus === 'Approved') ? 'approval' : 'rejection';
                $notifTitle   = ($newStatus === 'Approved') ? "Booking Approved: {$venueName}" : "Booking Request Declined: {$venueName}";
                $notifMsg     = ($newStatus === 'Approved')
                    ? "Your reservation for {$venueName} on {$bDate} has been officially approved by Campus Administration."
                    : "Your reservation request for {$venueName} on {$bDate} was declined by Campus Administration.";
                $nStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, time_ago, is_read) VALUES (?, ?, ?, ?, 'Just now', 0)");
                $nStmt->execute([$targetUserId, $notifTitle, $notifMsg, $notifType]);
            }
        } catch (Throwable $e) {}
    }
    header('Location: requests.php');
    exit;
}

// Handle cancelling a pending request
if (isset($_GET['cancel'])) {
    $cancelId = (int)$_GET['cancel'];

    if ($pdo) {
        try {
            // Clean up uploaded attachment if present
            $stmt = $pdo->prepare("SELECT attachment FROM requests WHERE id = ? AND status = 'Pending'");
            $stmt->execute([$cancelId]);
            $item = $stmt->fetch();

            if ($item && !empty($item['attachment'])) {
                $filePath = __DIR__ . '/uploads/' . $item['attachment'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            $delStmt = $pdo->prepare("DELETE FROM requests WHERE id = ? AND status = 'Pending'");
            $delStmt->execute([$cancelId]);
        } catch (Throwable $e) {}
    }

    header('Location: requests.php');
    exit;
}

$requests = [];
if ($pdo) {
    try {
        if ($isAdmin) {
            $stmt = $pdo->query("SELECT * FROM requests ORDER BY created_at DESC");
            $requests = $stmt->fetchAll();
        } else {
            $stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? OR user_id IS NULL ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            $requests = $stmt->fetchAll();
        }
    } catch (Throwable $e) {
        $requests = [];
    }
}

// Counts for filter tabs
$countPending  = 0;
$countApproved = 0;
$countRejected = 0;
foreach ($requests as $r) {
    if (($r['status'] ?? '') === 'Approved') $countApproved++;
    elseif (($r['status'] ?? '') === 'Rejected') $countRejected++;
    else $countPending++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Requests - VenueVibe</title>
  
  <!-- Google Font: Geist -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <!-- Stylesheet -->
  <link rel="stylesheet" href="requests.css">
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

          <a href="venue-list.php" class="nav-link">
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

          <a href="requests.php" class="nav-link active">
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
          <h1 class="nav-page-title">My Booking Requests</h1>
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

        <div class="panel">
          <div class="requests-header">
            <div>
              <h2>Your Booking Requests</h2>
              <div class="requests-subtext">Monitor and manage the status of all your submitted reservations.</div>
            </div>
            <div class="filter-tabs" id="filterTabs">
              <button type="button" class="filter-tab active" data-filter="all" onclick="filterRequests('all')">All (<?= count($requests) ?>)</button>
              <button type="button" class="filter-tab" data-filter="Pending" onclick="filterRequests('Pending')">Pending (<?= $countPending ?>)</button>
              <button type="button" class="filter-tab" data-filter="Approved" onclick="filterRequests('Approved')">Approved (<?= $countApproved ?>)</button>
              <button type="button" class="filter-tab" data-filter="Rejected" onclick="filterRequests('Rejected')">Rejected (<?= $countRejected ?>)</button>
            </div>
          </div>

          <div id="requestsList">
            <?php if (count($requests) === 0): ?>
              <div class="about-text">You have not submitted any booking requests yet. Browse venues to book your first space!</div>
            <?php else: ?>
              <?php foreach ($requests as $r): ?>
                <?php $status = $r['status'] ?: 'Pending'; ?>
                <div class="request-row" data-status="<?= htmlspecialchars($status) ?>">
                  <div>
                    <div class="request-venue">
                      <strong><?= htmlspecialchars($r['venue_name']) ?></strong> &bull; <?= htmlspecialchars($r['venue_location']) ?>
                    </div>
                    <div class="request-meta">
                      <i class="fa-regular fa-calendar"></i> <?= htmlspecialchars($r['booking_date']) ?>
                      &nbsp;&bull;&nbsp;
                      <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($r['booking_time']) ?>
                      &nbsp;&bull;&nbsp;
                      <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($r['purpose']) ?>
                    </div>
                    <?php if (!empty($r['attachment'])): ?>
                      <a class="request-attachment" href="uploads/<?= htmlspecialchars($r['attachment']) ?>" target="_blank">
                        <i class="fa-solid fa-paperclip"></i> <?= htmlspecialchars($r['attachment']) ?>
                      </a>
                    <?php endif; ?>
                  </div>
                  <div class="request-action">
                    <?php if ($status === 'Approved'): ?>
                      <span class="status-approved"><i class="fa-solid fa-check"></i> Approved</span>
                      <?php $venueSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $r['venue_name']), '-')); ?>
                      <a href="venue-details.php?venue=<?= urlencode($venueSlug) ?>#add-review-section" style="font-size:12px; color:#4f46e5; text-decoration:none; margin-left:8px; font-weight:600; display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border:1px solid rgba(79,70,229,0.3); border-radius:6px; background:#eef2ff;" title="Share your feedback after using this venue">
                        ⭐ Review Venue
                      </a>
                    <?php elseif ($status === 'Rejected'): ?>
                      <span class="status-rejected"><i class="fa-solid fa-xmark"></i> Rejected</span>
                    <?php else: ?>
                      <span class="status-pending"><i class="fa-solid fa-hourglass-half"></i> Pending</span>
                      <?php if ($isAdmin): ?>
                        <a href="requests.php?action=approve&id=<?= (int)$r['id'] ?>" class="btn-approve" style="background:#10b981;color:#fff;padding:6px 12px;border-radius:var(--radius-md);font-size:12px;text-decoration:none;font-weight:600;margin-left:8px;display:inline-flex;align-items:center;gap:4px;" title="Approve booking"><i class="fa-solid fa-check"></i> Approve</a>
                        <a href="requests.php?action=reject&id=<?= (int)$r['id'] ?>" class="btn-reject" style="background:#ef4444;color:#fff;padding:6px 12px;border-radius:var(--radius-md);font-size:12px;text-decoration:none;font-weight:600;margin-left:6px;display:inline-flex;align-items:center;gap:4px;" title="Reject booking"><i class="fa-solid fa-xmark"></i> Reject</a>
                      <?php else: ?>
                        <button type="button" class="row-cancel-btn" onclick="cancelRequest(<?= (int) $r['id'] ?>)">Cancel</button>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
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
  <script src="requests.js"></script>
</body>
</html>
