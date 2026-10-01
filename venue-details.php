<?php
require_once 'config.php';

$userName     = $_SESSION['full_name']  ?? 'Fahim';
$userRole     = $_SESSION['role']       ?? 'Student';
$userEmail    = $_SESSION['email']      ?? 'fahim@uiu.ac.bd';
$studentId    = $_SESSION['student_id'] ?? '011 221 045';
$department   = $_SESSION['department'] ?? 'Computer Science & Engineering';
$avatarLetter = strtoupper(substr($userName, 0, 1));

$id = isset($_GET['venue']) ? $_GET['venue'] : null;

// Handle new review submission
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['submit_review'])) {
    $postVenueId = trim($_POST['venue_id'] ?? '');
    $postName    = trim($_POST['reviewer_name'] ?? $userName);
    $postComment = trim($_POST['review_text'] ?? '');
    
    if ($postVenueId !== '' && $postComment !== '') {
        // Calculate 2-letter initials (e.g. "Fahim Ahmed" -> "FA")
        $parts = preg_split('/\s+/', trim($postName));
        $initials = '';
        foreach ($parts as $p) {
            if ($p !== '') $initials .= strtoupper(substr($p, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        if ($initials === '') $initials = 'U';

        if ($pdo) {
            try {
                $insStmt = $pdo->prepare("INSERT INTO reviews (venue_id, initials, reviewer_name, time_ago, review_text) VALUES (?, ?, ?, 'Just now', ?)");
                $insStmt->execute([$postVenueId, $initials, $postName, $postComment]);

                $updStmt = $pdo->prepare("UPDATE venues SET review_count = review_count + 1 WHERE id = ?");
                $updStmt->execute([$postVenueId]);
            } catch (Throwable $e) {}
        }
        header("Location: venue-details.php?venue=" . urlencode($postVenueId) . "&review_status=success#feedback-section");
        exit;
    }
}

$venue = null;
$equipment = [];
$schedule = [];
$reviews = [];

if ($pdo) {
    try {
        if ($id === null || trim($id) === '') {
            $firstStmt = $pdo->query("SELECT id FROM venues ORDER BY name LIMIT 1");
            $firstRow = $firstStmt->fetch();
            $id = $firstRow ? $firstRow['id'] : null;
        }

        if ($id !== null) {
            $stmt = $pdo->prepare("SELECT * FROM venues WHERE id = ?");
            $stmt->execute([$id]);
            $venue = $stmt->fetch();

            if ($venue) {
                $eqStmt = $pdo->prepare("SELECT item FROM equipment WHERE venue_id = ?");
                $eqStmt->execute([$id]);
                $equipment = $eqStmt->fetchAll(PDO::FETCH_COLUMN);

                $schedStmt = $pdo->prepare("SELECT * FROM schedule WHERE venue_id = ? ORDER BY id");
                $schedStmt->execute([$id]);
                $schedule = $schedStmt->fetchAll();

                $revStmt = $pdo->prepare("SELECT * FROM reviews WHERE venue_id = ? ORDER BY id DESC");
                $revStmt->execute([$id]);
                $reviews = $revStmt->fetchAll();
            }
        }
    } catch (Throwable $e) {
        $venue = null;
    }
}

// Fallback venue data if database is offline or venue not found
if (!$venue) {
    $fallbackVenues = [
        'electrical-lab-4' => [
            'id' => 'electrical-lab-4',
            'name' => 'Electrical Lab 4',
            'type' => 'Lab',
            'location' => 'Main Campus, North Wing',
            'capacity' => 40,
            'status' => 'Available',
            'thumbnail' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=400&h=280&fit=crop',
            'image' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=1000&h=600&fit=crop',
            'rating' => 4.5,
            'infrastructure' => 'Normal',
            'about' => 'Electrical Lab 4 is a fully equipped lab space for circuit design, testing, and hands-on electrical engineering coursework. Located in the North Wing of the Main Campus, it offers individual workstations with power outlets and safety equipment for practical sessions.'
        ],
        'uiu-multi-purpose-hall' => [
            'id' => 'uiu-multi-purpose-hall',
            'name' => 'UIU Multipurpose Hall',
            'type' => 'Lecture Hall',
            'location' => 'Ground Floor',
            'capacity' => 250,
            'status' => 'Available',
            'thumbnail' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=400&h=280&fit=crop',
            'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=1000&h=600&fit=crop',
            'rating' => 4.9,
            'infrastructure' => 'Normal',
            'about' => 'The UIU Multipurpose Hall serves as the central hub for grand events, cultural performances, and major academic gatherings at the United International University permanent campus.'
        ],
        'uiu-design-lab' => [
            'id' => 'uiu-design-lab',
            'name' => 'UIU Design Lab',
            'type' => 'Lab',
            'location' => 'Main Campus',
            'capacity' => 35,
            'status' => 'Available',
            'thumbnail' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=400&h=280&fit=crop',
            'image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1000&h=600&fit=crop',
            'rating' => 4.6,
            'infrastructure' => 'Normal',
            'about' => 'UIU Design Lab is built for creative and technical design work, with computer workstations, drawing tablets, and 3D printing tools for prototyping student projects.'
        ],
        'media-class' => [
            'id' => 'media-class',
            'name' => 'Media Class',
            'type' => 'Seminar',
            'location' => '8th Floor',
            'capacity' => 20,
            'status' => 'Booked',
            'thumbnail' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&h=280&fit=crop',
            'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1000&h=600&fit=crop',
            'rating' => 4.3,
            'infrastructure' => 'Normal',
            'about' => 'Media Class is a small studio-style room for video production, podcast recording, and media coursework, equipped with cameras, microphones, and editing stations.'
        ],
        'uiu-innovation-hub' => [
            'id' => 'uiu-innovation-hub',
            'name' => 'UIU Innovation Hub',
            'type' => 'Seminar',
            'location' => 'iRIIC, 10th Floor',
            'capacity' => 120,
            'status' => 'Available',
            'thumbnail' => 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=400&h=280&fit=crop',
            'image' => 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=1000&h=600&fit=crop',
            'rating' => 4.8,
            'infrastructure' => 'Advanced',
            'about' => 'The UIU Innovation Hub is a space for students to share ideas, work together, and develop new projects at United International University.'
        ],
        'uiu-hall-2' => [
            'id' => 'uiu-hall-2',
            'name' => 'UIU Hall-2',
            'type' => 'Lecture Hall',
            'location' => '6th Floor',
            'capacity' => 150,
            'status' => 'Booked',
            'thumbnail' => 'https://images.unsplash.com/photo-1560439514-4e9645039924?w=400&h=280&fit=crop',
            'image' => 'https://images.unsplash.com/photo-1560439514-4e9645039924?w=1000&h=600&fit=crop',
            'rating' => 4.4,
            'infrastructure' => 'Normal',
            'about' => 'UIU Hall-2 is a large lecture hall on the 6th Floor, suitable for lectures, seminars, and mid-sized events, equipped with a sound system and stage lighting.'
        ]
    ];

    $venueKey = ($id && isset($fallbackVenues[$id])) ? $id : 'electrical-lab-4';
    $venue = $fallbackVenues[$venueKey];
    $equipment = ['High-Speed Gigabit LAN', 'Laser Projectors', 'Wireless Microphone Set', 'Air Conditioning', 'Motorized Screen'];
    $schedule = [
        [
            'event_date' => 'Tomorrow',
            'title' => 'Circuits to Solutions Workshop',
            'time_range' => '09:00 AM – 11:30 AM',
            'host' => 'Mr. Md. Abid Hossain',
            'status' => 'confirmed'
        ],
        [
            'event_date' => 'Friday',
            'title' => 'Faculty Research Colloquium',
            'time_range' => '02:00 PM – 04:30 PM',
            'host' => 'Dept. of CSE',
            'status' => 'tentative'
        ]
    ];
    $review = [
        'reviewer_name' => 'Dr. Hasan Sarwar',
        'initials' => 'HS',
        'time_ago' => '2 days ago',
        'review_text' => 'Excellent venue setup and acoustic clarity. Everything operated smoothly for our session.'
    ];
}

if (empty($reviews) && !empty($review)) {
    $reviews = [$review];
}

$badgeClass = ($venue['status'] === 'Booked') ? 'badge-booked' : 'badge-available';

$bookingUrl = "request-booking.php?"
    . "name=" . urlencode($venue['name'])
    . "&location=" . urlencode($venue['location'])
    . "&capacity=" . urlencode($venue['capacity'] . " Seats")
    . "&image=" . urlencode($venue['thumbnail'])
    . "&status=" . urlencode($venue['status'])
    . "&from=" . urlencode("venue-details.php?venue=" . $venue['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($venue['name']) ?> - VenueVibe</title>
  
  <!-- Google Font: Geist -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <!-- Stylesheet -->
  <link rel="stylesheet" href="venue-details.css">
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
          <h1 class="nav-page-title">Venue Details</h1>
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

        <div class="details-content">

          <!-- Back to venues breadcrumb / link -->
          <div style="margin-bottom: 16px;">
            <a href="venue-list.php" style="display: inline-flex; align-items: center; gap: 8px; font-size: var(--text-sm); font-weight: 600; color: var(--text-muted); text-decoration: none; transition: var(--transition-default);" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
              <i class="fa-solid fa-arrow-left"></i> Back to Venues
            </a>
          </div>

          <div class="hero-wrap">
            <img class="hero-image" src="<?= htmlspecialchars($venue['image']) ?>" alt="<?= htmlspecialchars($venue['name']) ?>">
            <div class="hero-overlay"></div>
          </div>

          <div class="venue-heading">
            <div class="heading-top-row">
              <span class="<?= $badgeClass ?>"><?= htmlspecialchars($venue['status']) ?></span>
              <span class="rating-pill"><i class="fa-solid fa-star"></i> <?= number_format($venue['rating'], 1) ?></span>
            </div>
            <h1><?= htmlspecialchars($venue['name']) ?></h1>
            <div class="location-line">
              <i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($venue['location']) ?>
            </div>
            <a class="submit-btn book-btn" href="<?= htmlspecialchars($bookingUrl) ?>">
              <i class="fa-solid fa-calendar-plus"></i> Book This Venue
            </a>
          </div>

          <div class="stats-row">
            <div class="stat-card">
              <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
              <div class="stat-label">Capacity</div>
              <div class="stat-value"><?= (int)$venue['capacity'] ?> Seats</div>
            </div>
            <div class="stat-card">
              <div class="stat-icon"><i class="fa-solid fa-microchip"></i></div>
              <div class="stat-label">Infrastructure</div>
              <div class="stat-value"><?= htmlspecialchars($venue['infrastructure']) ?></div>
            </div>
            <div class="stat-card">
              <div class="stat-icon"><i class="fa-solid fa-star"></i></div>
              <div class="stat-label">User Rating</div>
              <div class="stat-value"><?= number_format($venue['rating'], 1) ?> / 5.0</div>
            </div>
          </div>

          <div class="panel">
            <h2>Venue Equipment</h2>
            <div>
              <?php foreach ($equipment as $item): ?>
                <span class="equipment-tag"><i class="fa-solid fa-check"></i> <?= htmlspecialchars($item) ?></span>
              <?php endforeach; ?>
            </div>

            <h3>About This Space</h3>
            <div class="about-text"><?= htmlspecialchars($venue['about']) ?></div>
          </div>

          <div class="panel">
            <h2>Upcoming Schedule</h2>
            <?php if (empty($schedule)): ?>
              <div class="about-text">No upcoming events scheduled for this venue.</div>
            <?php else: ?>
              <?php foreach ($schedule as $item): ?>
                <?php
                  $statusClass = ($item['status'] === 'confirmed') ? 'status-confirmed' : 'status-tentative';
                  $statusLabel = ($item['status'] === 'confirmed') ? 'Confirmed' : 'Tentative';
                ?>
                <div class="schedule-box">
                  <div>
                    <h4><?= htmlspecialchars($item['event_date']) ?></h4> <?= htmlspecialchars($item['title']) ?>
                    <br>
                    <span class="venue-meta"><?= htmlspecialchars($item['time_range']) ?> · Host: <?= htmlspecialchars($item['host']) ?></span>
                  </div>
                  <span class="<?= $statusClass ?>"><?= $statusLabel ?></span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="panel" id="feedback-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h2 style="margin: 0;">Recent Feedback</h2>
              <span style="font-size: 0.85rem; color: var(--slate-500); font-weight: 500;"><?= count($reviews) ?> <?= count($reviews) === 1 ? 'Review' : 'Reviews' ?></span>
            </div>

            <?php if (isset($_GET['review_status']) && $_GET['review_status'] === 'success'): ?>
              <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: var(--radius-lg, 10px); margin-bottom: 16px; font-size: 0.875rem; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>Thank you! Your feedback for this venue has been recorded successfully.</span>
              </div>
            <?php endif; ?>

            <?php if (!empty($reviews)): ?>
              <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                <?php foreach ($reviews as $rev): ?>
                <div class="review-box">
                  <div class="review-circle"><?= htmlspecialchars($rev['initials']) ?></div>
                  <div class="review-text-content" style="flex: 1;">
                    <span class="review-author"><?= htmlspecialchars($rev['reviewer_name']) ?></span>
                    <span class="review-time"> · <?= htmlspecialchars($rev['time_ago']) ?></span>
                    <p style="margin: 6px 0 0;"><?= htmlspecialchars($rev['review_text']) ?></p>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Small Section for Adding Venue Review After Using -->
            <div id="add-review-section" style="background: var(--slate-50, #f8fafc); border: 1px dashed var(--slate-300, #cbd5e1); border-radius: var(--radius-xl, 14px); padding: 18px;">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <h3 style="margin: 0; font-size: 0.95rem; font-weight: 600; color: var(--slate-900, #0f172a);">Add Your Feedback</h3>
                <span style="font-size: 0.75rem; color: var(--slate-500, #64748b); background: #ffffff; padding: 2px 8px; border-radius: 9999px; border: 1px solid var(--border-default, #e2e8f0);">After Using Venue</span>
              </div>

              <form action="venue-details.php?venue=<?= urlencode($venue['id']) ?>" method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                <input type="hidden" name="venue_id" value="<?= htmlspecialchars($venue['id']) ?>">
                <input type="hidden" name="submit_review" value="1">

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                  <div style="flex: 1; min-width: 180px;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--slate-600, #475569); margin-bottom: 4px;">Reviewer Name</label>
                    <input type="text" name="reviewer_name" value="<?= htmlspecialchars($userName) ?>" required style="width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid var(--border-default, #e2e8f0); border-radius: var(--radius-md, 8px); font-size: 0.875rem; background: #ffffff; color: var(--slate-900, #0f172a);">
                  </div>
                  <div style="width: 170px;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--slate-600, #475569); margin-bottom: 4px;">Experience Rating</label>
                    <select name="rating" style="width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid var(--border-default, #e2e8f0); border-radius: var(--radius-md, 8px); font-size: 0.875rem; background: #ffffff; color: var(--slate-900, #0f172a);">
                      <option value="5">⭐⭐⭐⭐⭐ 5.0</option>
                      <option value="4">⭐⭐⭐⭐ 4.0</option>
                      <option value="3">⭐⭐⭐ 3.0</option>
                      <option value="2">⭐⭐ 2.0</option>
                      <option value="1">⭐ 1.0</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--slate-600, #475569); margin-bottom: 4px;">Experience & Feedback</label>
                  <textarea name="review_text" rows="3" placeholder="Share your experience using this venue (e.g. equipment performance, acoustics, seating, AC)..." required style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid var(--border-default, #e2e8f0); border-radius: var(--radius-md, 8px); font-size: 0.875rem; font-family: inherit; resize: vertical; background: #ffffff; color: var(--slate-900, #0f172a);"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                  <button type="submit" class="btn btn-primary" style="padding: 9px 20px; font-size: 0.875rem; font-weight: 600; border-radius: var(--radius-md, 8px); background: var(--primary, #6366f1); color: #ffffff; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"></path><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    Submit Feedback
                  </button>
                </div>
              </form>
            </div>
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
  <script src="venue-details.js"></script>
</body>
</html>
