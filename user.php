<?php
/* ============================================================
   VenueVibe - user.php
   Dynamic User Dashboard backed by Database and Session
   ============================================================ */

require_once 'config.php';

// Retrieve session user data, with safe fallback to demo profile
$isLoggedIn = isset($_SESSION['user_id']);
$userId     = $_SESSION['user_id']     ?? 2;
$userName   = $_SESSION['full_name']   ?? 'Fahim Ahmed';
$userEmail  = $_SESSION['email']       ?? 'fahim@uiu.ac.bd';
$userRole   = $_SESSION['role']        ?? 'Student';
$studentId  = $_SESSION['student_id']  ?? '011 221 045';
$department = $_SESSION['department']  ?? 'Computer Science & Engineering';
$userPhone  = $_SESSION['phone']       ?? '+880 1712-345678';
$avatarLetter = strtoupper(substr($userName, 0, 1));

$isAdmin    = !empty($_SESSION['is_admin']) || (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin');

// Retrieve real booking counts from database
try {
    if ($pdo) {
        if ($isAdmin) {
            $totalCountStmt = $pdo->query("SELECT COUNT(*) FROM requests");
            $totalBookings  = (int)$totalCountStmt->fetchColumn();

            $pendingCountStmt = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'Pending'");
            $pendingRequests  = (int)$pendingCountStmt->fetchColumn();

            $approvedCountStmt = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'Approved'");
            $acceptedBookings  = (int)$approvedCountStmt->fetchColumn();

            $bookingsStmt = $pdo->query("SELECT * FROM requests ORDER BY booking_date DESC, id DESC LIMIT 4");
            $upcomingBookings = $bookingsStmt->fetchAll();

            $notifStmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5");
            $notifications = $notifStmt->fetchAll();
        } else {
            $totalCountStmt = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE user_id = ? OR user_id IS NULL");
            $totalCountStmt->execute([$userId]);
            $totalBookings  = (int)$totalCountStmt->fetchColumn();

            $pendingCountStmt = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE (user_id = ? OR user_id IS NULL) AND status = 'Pending'");
            $pendingCountStmt->execute([$userId]);
            $pendingRequests  = (int)$pendingCountStmt->fetchColumn();

            $approvedCountStmt = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE (user_id = ? OR user_id IS NULL) AND status = 'Approved'");
            $approvedCountStmt->execute([$userId]);
            $acceptedBookings  = (int)$approvedCountStmt->fetchColumn();

            $bookingsStmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? OR user_id IS NULL ORDER BY booking_date DESC, id DESC LIMIT 4");
            $bookingsStmt->execute([$userId]);
            $upcomingBookings = $bookingsStmt->fetchAll();

            $notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? OR user_id IS NULL ORDER BY created_at DESC LIMIT 5");
            $notifStmt->execute([$userId]);
            $notifications = $notifStmt->fetchAll();
        }
    } else {
        throw new Exception("Database is not connected.");
    }
} catch (Throwable $e) {
    // Graceful fallback if database tables are not yet migrated or offline
    $totalBookings = 24;
    $pendingRequests = 3;
    $acceptedBookings = 8;
    $upcomingBookings = [];
    $notifications = [];
}

// Prepare dynamic calendar events from real bookings
$calendarEvents = [];
if (!empty($upcomingBookings)) {
    foreach ($upcomingBookings as $bk) {
        $bDate = $bk['booking_date'] ?? '';
        if ($bDate) {
            $parts = explode('-', $bDate);
            if (count($parts) === 3) {
                $y = (int)$parts[0];
                $m = (int)$parts[1] - 1; // 0-indexed for JS Date
                $d = (int)$parts[2];
                $k = "$y-$m-$d";
                $st = strtolower($bk['status'] ?? '');
                $type = ($st === 'approved') ? 'green' : (($st === 'pending') ? 'red' : 'purple');
                $calendarEvents[$k] = [
                    'type' => $type,
                    'title' => ($bk['venue_name'] ?? 'Venue') . ' (' . ($bk['purpose'] ?? 'Booking') . ')',
                    'status' => $bk['status'] ?? 'Confirmed'
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VenueVibe - User Dashboard</title>
  <!-- Google Fonts: Geist -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="user.css">
</head>
<body>

  <!-- ================= APP CONTAINER ================= -->
  <div class="app-container">

    <!-- ================= SIDEBAR ================= -->
    <aside class="sidebar">
      <div class="sidebar-top">
        <!-- Brand Wordmark -->
        <div class="brand">
          <span class="brand-text">VenueVibe</span>
        </div>

        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
          <a href="user.php" class="nav-link active">
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

    <!-- ================= MAIN CONTENT AREA ================= -->
    <main class="main-wrapper">

      <!-- ================= ELEVATED MODERN NAVBAR ================= -->
      <header class="top-header">
        <div class="header-left">
          <h1 class="nav-page-title">User Dashboard</h1>
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

      <!-- ================= DASHBOARD BODY ================= -->
      <div class="dashboard-content">

        <!-- Welcome Row with Primary Action Button -->
        <div class="welcome-row">
          <div>
            <h1 class="page-title">Welcome back, <?= htmlspecialchars($userName) ?></h1>
            <p class="page-subtitle">Here's what's happening with your bookings & campus facilities today.</p>
          </div>
          <button class="btn-primary-action" id="bookVenueActionBtn" onclick="window.location.href='venue-list.php'" type="button">
            <svg class="btn-icon" viewBox="0 0 24 24">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Book a Venue</span>
          </button>
        </div>

        <!-- ---------- ROW 1: STAT CARDS ---------- -->
        <div class="stats-grid">

          <!-- Card 1: Total Bookings -->
          <div class="stat-card">
            <div class="stat-icon-box purple-box">
              <svg class="stat-svg" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
            </div>
            <div class="stat-details">
              <span class="stat-label">Total Bookings</span>
              <h2 class="stat-value"><?= $totalBookings ?></h2>
            </div>
          </div>

          <!-- Card 2: Pending Requests -->
          <div class="stat-card">
            <div class="stat-icon-box green-box">
              <svg class="stat-svg" viewBox="0 0 24 24">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                <line x1="9" y1="11" x2="15" y2="11"></line>
                <line x1="9" y1="15" x2="13" y2="15"></line>
              </svg>
            </div>
            <div class="stat-details">
              <span class="stat-label">Pending Requests</span>
              <h2 class="stat-value" id="pendingStatCount"><?= $pendingRequests ?></h2>
            </div>
          </div>

          <!-- Card 3: Accepted Bookings -->
          <div class="stat-card">
            <div class="stat-icon-box blue-box">
              <svg class="stat-svg" viewBox="0 0 24 24">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg>
            </div>
            <div class="stat-details">
              <span class="stat-label">Accepted Bookings</span>
              <h2 class="stat-value" id="acceptedStatCount"><?= $acceptedBookings ?></h2>
            </div>
          </div>

        </div>

        <!-- ---------- ROW 2: UPCOMING BOOKINGS & CALENDAR VIEW ---------- -->
        <div class="middle-grid">

          <!-- Left: My Upcoming Bookings -->
          <section class="card upcoming-card">
            <div class="card-header">
              <div class="card-title-group">
                <h2 class="card-title">My Upcoming Bookings</h2>
                <span class="pill-badge purple-pill" id="bookingCountBadge"><?= count($upcomingBookings) ?> upcoming</span>
              </div>
              <a href="requests.php" class="see-all-link" id="viewAllBookingsLink">View All</a>
            </div>

            <div class="booking-list" id="bookingList">
              <?php if (!empty($upcomingBookings)): ?>
                <?php foreach ($upcomingBookings as $b): 
                  $isPending = ($b['status'] === 'Pending');
                  $pillClass = $isPending ? 'status-pending' : ($b['status'] === 'Rejected' ? 'status-rejected' : 'status-approved');
                  $boxClass  = $isPending ? 'amber-soft-box' : 'purple-soft-box';
                ?>
                <div class="booking-item">
                  <div class="booking-icon-box <?= $boxClass ?>">
                    <svg class="item-svg" viewBox="0 0 24 24">
                      <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                      <line x1="9" y1="6" x2="9.01" y2="6"></line>
                      <line x1="15" y1="6" x2="15.01" y2="6"></line>
                      <line x1="9" y1="10" x2="9.01" y2="10"></line>
                      <line x1="15" y1="10" x2="15.01" y2="10"></line>
                      <line x1="9" y1="14" x2="9.01" y2="14"></line>
                    </svg>
                  </div>
                  <div class="booking-info">
                    <h4><?= htmlspecialchars($b['venue_name']) ?></h4>
                    <p class="booking-time"><?= htmlspecialchars($b['booking_date']) ?> • <?= htmlspecialchars($b['booking_time']) ?></p>
                    <span class="booking-desc"><?= htmlspecialchars($b['purpose']) ?> • <?= htmlspecialchars($b['venue_location']) ?></span>
                  </div>
                  <div class="booking-actions">
                    <span class="status-pill <?= $pillClass ?>"><?= htmlspecialchars($b['status']) ?></span>
                    <button class="btn-details" 
                            data-booking="<?= htmlspecialchars($b['venue_name']) ?>" 
                            data-time="<?= htmlspecialchars($b['booking_date']) ?> • <?= htmlspecialchars($b['booking_time']) ?>" 
                            data-purpose="<?= htmlspecialchars($b['purpose']) ?>" 
                            data-status="<?= htmlspecialchars($b['status']) ?>"
                            type="button">VIEW DETAILS</button>
                  </div>
                </div>
                <?php endforeach; ?>
              <?php else: ?>
                <!-- Default seeded demonstration items -->
                <div class="booking-item">
                  <div class="booking-icon-box purple-soft-box">
                    <svg class="item-svg" viewBox="0 0 24 24">
                      <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                      <line x1="9" y1="6" x2="9.01" y2="6"></line>
                      <line x1="15" y1="6" x2="15.01" y2="6"></line>
                      <line x1="9" y1="10" x2="9.01" y2="10"></line>
                      <line x1="15" y1="10" x2="15.01" y2="10"></line>
                      <line x1="9" y1="14" x2="9.01" y2="14"></line>
                    </svg>
                  </div>
                  <div class="booking-info">
                    <h4>UIU Innovation Hub</h4>
                    <p class="booking-time">Jul 12, 2025 • 2:00 PM – 4:00 PM</p>
                    <span class="booking-desc">Club Seminar • 150 Attendees • Projector & Mic</span>
                  </div>
                  <div class="booking-actions">
                    <span class="status-pill status-approved">Approved</span>
                    <button class="btn-details" data-booking="UIU Innovation Hub" data-time="Jul 12, 2025 • 2:00 PM – 4:00 PM" data-purpose="Club Seminar (150 Attendees)" data-status="Approved" type="button">VIEW DETAILS</button>
                  </div>
                </div>

                <div class="booking-item">
                  <div class="booking-icon-box amber-soft-box">
                    <svg class="item-svg" viewBox="0 0 24 24">
                      <line x1="3" y1="22" x2="21" y2="22"></line>
                      <line x1="6" y1="18" x2="6" y2="11"></line>
                      <line x1="10" y1="18" x2="10" y2="11"></line>
                      <line x1="14" y1="18" x2="14" y2="11"></line>
                      <line x1="18" y1="18" x2="18" y2="11"></line>
                      <polygon points="12 2 20 7 4 7 12 2"></polygon>
                    </svg>
                  </div>
                  <div class="booking-info">
                    <h4>UIU Multipurpose Hall</h4>
                    <p class="booking-time">Jul 18, 2025 • 10:00 AM – 4:00 PM</p>
                    <span class="booking-desc">Tech Fest Exhibition • 300 Attendees • Full Hall</span>
                  </div>
                  <div class="booking-actions">
                    <span class="status-pill status-pending">Pending</span>
                    <button class="btn-details" data-booking="UIU Multipurpose Hall" data-time="Jul 18, 2025 • 10:00 AM – 4:00 PM" data-purpose="Tech Fest Exhibition (300 Attendees)" data-status="Pending" type="button">VIEW DETAILS</button>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </section>

          <!-- Right: Compact Calendar View -->
          <section class="card calendar-card">
            <div class="card-header">
              <h2 class="card-title">Calendar View</h2>
            </div>

            <!-- Month Navigation -->
            <div class="calendar-nav-bar">
              <button class="cal-nav-btn" id="prevMonthBtn" title="Previous Month" type="button">‹</button>
              <span class="cal-month-title" id="calendarMonthTitle"><?= date('F Y') ?></span>
              <button class="cal-nav-btn" id="nextMonthBtn" title="Next Month" type="button">›</button>
            </div>

            <!-- Day of week headers -->
            <div class="calendar-weekdays-row">
              <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
            </div>

            <!-- Dynamic Days Grid -->
            <div class="calendar-days-grid" id="calendarGrid">
              <!-- Injected by JavaScript -->
            </div>

            <!-- Event Legend (Booked, Confirmed, Pending) -->
            <div class="calendar-legend-row">
              <span class="legend-item"><i class="legend-dot purple-dot"></i> Booked</span>
              <span class="legend-item"><i class="legend-dot green-dot"></i> Confirmed</span>
              <span class="legend-item"><i class="legend-dot red-dot"></i> Pending</span>
            </div>

            <!-- Upcoming Events List -->
            <div class="calendar-events-section">
              <h3 class="events-subheading">Upcoming Events</h3>
              <div class="events-list">
                <?php if (!empty($upcomingBookings)): ?>
                  <?php foreach (array_slice($upcomingBookings, 0, 3) as $ev): 
                    $evStatus = strtolower($ev['status'] ?? 'confirmed');
                    $badgeClass = ($evStatus === 'approved' || $evStatus === 'confirmed') ? 'badge-confirmed' : (($evStatus === 'pending') ? 'badge-pending' : 'badge-booked');
                    $cardClass = ($evStatus === 'approved' || $evStatus === 'confirmed') ? 'event-green' : (($evStatus === 'pending') ? 'event-amber' : 'event-purple');
                    $formattedDate = !empty($ev['booking_date']) ? date('M j, Y', strtotime($ev['booking_date'])) : date('M j, Y');
                  ?>
                  <div class="event-card-item <?= $cardClass ?>" data-toast="Event: <?= htmlspecialchars($ev['purpose'] ?? $ev['venue_name']) ?> on <?= $formattedDate ?>">
                    <div class="event-details">
                      <strong><?= htmlspecialchars($ev['purpose'] ?? $ev['venue_name']) ?></strong>
                      <span><?= $formattedDate ?></span>
                    </div>
                    <span class="event-badge <?= $badgeClass ?>"><?= strtoupper(htmlspecialchars($ev['status'] ?? 'CONFIRMED')) ?></span>
                  </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="event-card-item event-green" data-toast="Event: Campus Orientation on <?= date('M j, Y') ?>">
                    <div class="event-details">
                      <strong>Campus Orientation & Booking</strong>
                      <span><?= date('M j, Y') ?></span>
                    </div>
                    <span class="event-badge badge-confirmed">ACTIVE</span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </section>

        </div><!-- /middle-grid -->

        <!-- ---------- ROW 3: RECENT NOTIFICATIONS & VENUE HELPDESK ---------- -->
        <div class="bottom-vertical-flow">

          <!-- Card: Recent Notifications -->
          <section class="card notifications-card" id="notificationsSection">
            <div class="card-header">
              <div class="card-title-group">
                <h2 class="card-title">Recent Notifications</h2>
                <div class="note-filter-tabs">
                  <button class="filter-tab active" data-filter="all" type="button">All (<span id="countAllNotes"><?= max(count($notifications), 3) ?></span>)</button>
                  <button class="filter-tab" data-filter="unread" type="button">Unread (<span id="countUnreadNotes">2</span>)</button>
                </div>
              </div>
              <div class="header-actions">
                <a href="notifications.php" class="btn-header-link">Full Portal ↗</a>
                <button class="btn-header-link" id="markAllReadBtn" type="button">Mark all as read</button>
              </div>
            </div>

            <!-- Interactive Notification List -->
            <div class="notification-list" id="notificationList">
              <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $n): 
                  $isUnread = !$n['is_read'];
                  $type = $n['type'];
                  $iconBoxClass = ($type === 'approval') ? 'note-purple' : (($type === 'rejection') ? 'note-red' : 'note-gray');
                ?>
                <div class="notification-item <?= $isUnread ? 'unread active-note' : '' ?>" data-id="note-<?= $n['id'] ?>" data-title="<?= htmlspecialchars($n['title']) ?>" data-time="<?= htmlspecialchars($n['time_ago']) ?>" data-fulltext="<?= htmlspecialchars($n['message']) ?>">
                  <div class="note-icon-box <?= $iconBoxClass ?>">
                    <svg class="note-svg" viewBox="0 0 24 24">
                      <?php if ($type === 'approval'): ?>
                        <polyline points="20 6 9 17 4 12"></polyline>
                      <?php elseif ($type === 'rejection'): ?>
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                      <?php else: ?>
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                      <?php endif; ?>
                    </svg>
                  </div>
                  <div class="notification-body">
                    <div class="note-title-row">
                      <h4><?= htmlspecialchars($n['title']) ?></h4>
                      <?php if ($isUnread): ?><span class="unread-dot" title="Unread"></span><?php endif; ?>
                    </div>
                    <p><?= htmlspecialchars($n['message']) ?></p>
                  </div>
                  <div class="note-side-meta">
                    <span class="notification-time"><?= htmlspecialchars($n['time_ago']) ?></span>
                    <button class="btn-note-dismiss" title="Dismiss notification" type="button">✕</button>
                  </div>
                </div>
                <?php endforeach; ?>
              <?php else: ?>
                <!-- Fallback notifications -->
                <div class="notification-item unread" data-id="note-1" data-title="Campus-wide Maintenance" data-time="1 hour ago" data-fulltext="Main auditorium and GB4 will be closed from Sep 18 to Sep 21 for comprehensive audiovisual and stage lighting upgrades. Alternative halls are open for booking.">
                  <div class="note-icon-box note-gray">
                    <svg class="note-svg" viewBox="0 0 24 24">
                      <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                    </svg>
                  </div>
                  <div class="notification-body">
                    <div class="note-title-row">
                      <h4>Campus-wide Maintenance</h4>
                      <span class="unread-dot" title="Unread"></span>
                    </div>
                    <p>Main auditorium closed for audiovisual upgrades.</p>
                  </div>
                  <div class="note-side-meta">
                    <span class="notification-time">1 hour ago</span>
                    <button class="btn-note-dismiss" title="Dismiss notification" type="button">✕</button>
                  </div>
                </div>

                <div class="notification-item unread active-note" data-id="note-2" data-title="Your Booking Request is Accepted" data-time="2 hours ago" data-fulltext="Your reservation for UIU Innovation Hub on Jul 12, 2025 has been officially approved by Campus Administration. Hall keys can be collected at GB 101.">
                  <div class="note-icon-box note-purple">
                    <svg class="note-svg" viewBox="0 0 24 24">
                      <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                  </div>
                  <div class="notification-body">
                    <div class="note-title-row">
                      <h4>Your Booking Request is Accepted</h4>
                      <span class="unread-dot" title="Unread"></span>
                    </div>
                    <p>This is to inform you that your booking request has been approved.</p>
                  </div>
                  <div class="note-side-meta">
                    <span class="notification-time">2 hours ago</span>
                    <button class="btn-note-dismiss" title="Dismiss notification" type="button">✕</button>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </section>

        </div><!-- /bottom-vertical-flow -->

      </div><!-- /dashboard-content -->

      <!-- ================= SIMPLIFIED MODERN FOOTER ================= -->
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
              <a href="#" data-toast="Facilities Guidelines opened">Guidelines</a>
              <a href="#" data-toast="Campus Directory opened">Campus Directory</a>
              <a href="#" data-toast="Support Desk: venue@uiu.ac.bd">Helpdesk</a>
              <a href="#" data-toast="Terms & Privacy opened">Terms & Privacy</a>
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
          <span class="detail-value" id="profileDetailStudentId"><?= htmlspecialchars($studentId) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Email</span>
          <span class="detail-value" id="profileDetailEmail"><?= htmlspecialchars($userEmail) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Department</span>
          <span class="detail-value" id="profileDetailDepartment"><?= htmlspecialchars($department) ?></span>
        </div>
      </div>

      <div class="profile-actions">
        <button class="btn-profile-edit" id="btnOpenEditProfile" type="button">Edit Profile</button>
        <button class="btn-profile-logout" id="profileLogoutBtn" onclick="window.location.href='logout.php'" type="button">
          <svg class="logout-icon" viewBox="0 0 24 24">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          Logout
        </button>
      </div>
    </div>
  </div>

  <!-- ================= EDIT PROFILE MODAL ================= -->
  <div class="profile-modal-overlay" id="editProfileModalOverlay">
    <div class="profile-card" id="editProfileCard" style="max-width: 440px;">
      <button class="modal-close-btn" id="closeEditProfileModal" title="Close" type="button">
        <svg class="icon-close" viewBox="0 0 24 24">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>

      <div class="profile-header" style="margin-bottom: 20px;">
        <h3 class="profile-name" style="font-size: 1.25rem;">Edit Profile</h3>
        <p style="font-size: 0.85rem; color: var(--slate-500); margin: 4px 0 0;">Update your profile information</p>
      </div>

      <form id="editProfileForm" action="update-profile.php" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
        <div>
          <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--slate-700); margin-bottom: 5px;">Full Name</label>
          <input type="text" name="full_name" id="editFullName" value="<?= htmlspecialchars($userName) ?>" required style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid var(--border-default); border-radius: var(--radius-md); font-size: 0.875rem;">
        </div>

        <div>
          <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--slate-700); margin-bottom: 5px;">Student / User ID</label>
          <input type="text" name="student_id" id="editStudentId" value="<?= htmlspecialchars($studentId) ?>" required style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid var(--border-default); border-radius: var(--radius-md); font-size: 0.875rem;">
        </div>

        <div>
          <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--slate-700); margin-bottom: 5px;">Department</label>
          <input type="text" name="department" id="editDepartment" value="<?= htmlspecialchars($department) ?>" required style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid var(--border-default); border-radius: var(--radius-md); font-size: 0.875rem;">
        </div>

        <div>
          <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--slate-700); margin-bottom: 5px;">University Email</label>
          <input type="email" name="email" id="editEmail" value="<?= htmlspecialchars($userEmail) ?>" required style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid var(--border-default); border-radius: var(--radius-md); font-size: 0.875rem;">
        </div>

        <div>
          <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--slate-700); margin-bottom: 5px;">Role</label>
          <select name="role" id="editRole" style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid var(--border-default); border-radius: var(--radius-md); font-size: 0.875rem; background: #fff;">
            <option value="student" <?= strtolower($userRole) === 'student' ? 'selected' : '' ?>>Student</option>
            <option value="faculty" <?= strtolower($userRole) === 'faculty' ? 'selected' : '' ?>>Faculty</option>
            <option value="admin" <?= strtolower($userRole) === 'admin' ? 'selected' : '' ?>>Admin</option>
          </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 10px;">
          <button type="button" id="btnCancelEditProfile" style="flex: 1; padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border-default); background: var(--slate-100); color: var(--slate-700); font-weight: 600; cursor: pointer;">Cancel</button>
          <button type="submit" id="btnSaveEditProfile" style="flex: 1; padding: 10px; border-radius: var(--radius-md); border: none; background: var(--primary); color: #fff; font-weight: 600; cursor: pointer;">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= NOTIFICATION DETAILS MODAL ================= -->
  <div class="profile-modal-overlay" id="notificationModalOverlay">
    <div class="profile-card" id="notificationDetailCard">
      <button class="modal-close-btn" id="closeNoteModal" type="button">✕</button>
      <div class="modal-header-section">
        <span class="modal-pill-tag" id="modalNoteTime">1 hour ago</span>
        <h3 id="modalNoteTitle">Notification Details</h3>
      </div>
      <div class="modal-body-section">
        <p id="modalNoteBody">Notification details will appear here...</p>
      </div>
      <div class="modal-actions-section">
        <button class="btn-help-secondary" id="btnNoteActionDismiss" type="button">Dismiss</button>
        <button class="btn-primary-action" id="btnNoteActionUnderstood" type="button">Understood</button>
      </div>
    </div>
  </div>

  <!-- ================= BOOKING DETAILS MODAL ================= -->
  <div class="profile-modal-overlay" id="bookingModalOverlay">
    <div class="profile-card" id="bookingDetailCard">
      <button class="modal-close-btn" id="closeBookingModal" type="button">✕</button>
      <div class="modal-header-section">
        <span class="status-pill status-approved" id="modalBookingStatus">Approved</span>
        <h3 id="modalBookingTitle">UIU Innovation Hub</h3>
      </div>
      <div class="profile-details-list">
        <div class="detail-row">
          <span class="detail-label">Schedule Time</span>
          <span class="detail-value" id="modalBookingTime">Jul 12, 2025 • 2:00 PM – 4:00 PM</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Event Purpose</span>
          <span class="detail-value" id="modalBookingPurpose">Club Seminar</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Coordinator</span>
          <span class="detail-value"><?= htmlspecialchars($userName) ?></span>
        </div>
      </div>
      <div class="profile-actions">
        <button class="btn-profile-edit" data-toast="Downloading booking voucher PDF..." type="button">Download Slip</button>
        <button class="btn-help-secondary" id="closeBookingDetailBtn" type="button">Close</button>
      </div>
    </div>
  </div>

  <!-- Toast Notification Box -->
  <div id="toast"></div>

  <script>
    window.SERVER_CALENDAR_EVENTS = <?= json_encode($calendarEvents) ?>;
  </script>
  <script src="user.js"></script>
</body>
</html>
