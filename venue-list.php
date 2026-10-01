<?php
require_once 'config.php';

$userName     = $_SESSION['full_name']  ?? 'Fahim';
$userRole     = $_SESSION['role']       ?? 'Student';
$userEmail    = $_SESSION['email']      ?? 'fahim@uiu.ac.bd';
$studentId    = $_SESSION['student_id'] ?? '011 221 045';
$department   = $_SESSION['department'] ?? 'Computer Science & Engineering';
$avatarLetter = strtoupper(substr($userName, 0, 1));

// Fetch every venue from the database (with graceful fallback if DB is offline)
$venues = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM venues ORDER BY name");
        $venues = $stmt->fetchAll();
    } catch (Throwable $e) {
        $venues = [];
    }
}

if (empty($venues)) {
    $venues = [
        [
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
            'about' => 'Electrical Lab 4 is a fully equipped lab space for circuit design, testing, and hands-on electrical engineering coursework.'
        ],
        [
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
            'about' => 'The UIU Multipurpose Hall serves as the central hub for grand events, cultural performances, and major academic gatherings.'
        ],
        [
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
            'about' => 'UIU Design Lab is built for creative and technical design work, with computer workstations, drawing tablets, and 3D printing tools.'
        ],
        [
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
            'about' => 'Media Class is a small studio-style room for video production, podcast recording, and media coursework.'
        ],
        [
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
            'about' => 'The UIU Innovation Hub is a space for students to share ideas, work together, and develop new projects.'
        ],
        [
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
            'about' => 'UIU Hall-2 is a large lecture hall on the 6th Floor, suitable for lectures, seminars, and mid-sized events.'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Venues - VenueVibe</title>
  
  <!-- Google Font: Geist -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <!-- Stylesheet -->
  <link rel="stylesheet" href="venue-list.css">
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
          <h1 class="nav-page-title">Venues</h1>
        </div>

        <div class="header-right">
          <!-- Search box inside header -->
          <div class="search-box">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="searchInput" class="search-input" onkeyup="searchVenue()" placeholder="Search by name, building, or room...">
          </div>

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

        <div class="filters">
          <div class="filter-pills-row">
            <span class="filter-pill active" onclick="filterType('all', this)">All Venues</span>
            <span class="filter-pill" onclick="filterType('Lab', this)">Labs</span>
            <span class="filter-pill" onclick="filterType('Lecture Hall', this)">Lecture Halls</span>
            <span class="filter-pill" onclick="filterType('Seminar', this)">Seminars</span>
          </div>
          <div>
            <span>Total Venues: <?= count($venues) ?></span>
          </div>
        </div>

        <div class="venue-grid" id="venueGrid">
          <?php foreach ($venues as $v): ?>
            <?php $badgeClass = ($v['status'] === 'Booked') ? 'badge-booked' : 'badge-available'; ?>
            <div class="venue-card" onclick="goToDetails('<?= htmlspecialchars($v['id']) ?>')">
              <div class="venue-image-wrap">
                <span class="<?= $badgeClass ?>"><?= htmlspecialchars($v['status']) ?></span>
                <img class="venue-image" src="<?= htmlspecialchars($v['thumbnail']) ?>" alt="<?= htmlspecialchars($v['name']) ?>">
              </div>
              <div class="card-body">
                <div class="card-title-row">
                  <div class="venue-title"><?= htmlspecialchars($v['name']) ?></div>
                  <div class="venue-type"><?= htmlspecialchars($v['type']) ?></div>
                </div>
                <div class="venue-meta"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($v['location']) ?></div>
                <div class="venue-meta"><i class="fa-solid fa-users"></i> Capacity: <?= (int)$v['capacity'] ?> People</div>
                <button type="button" class="view-btn">View Details</button>
              </div>
            </div>
          <?php endforeach; ?>
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
  <script src="venue-list.js"></script>
</body>
</html>
