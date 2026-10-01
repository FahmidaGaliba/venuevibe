-- ============================================================
-- VenueVibe - Data Insertion Script (Seed Data)
-- File: insert_data.sql
-- ============================================================

USE venuevibe;

-- ------------------------------------------------------------
-- 1. SEED USERS
-- Default password for all seed accounts is: password123
-- ------------------------------------------------------------
INSERT INTO users (full_name, email, password_hash, role, is_admin, student_id, department, phone) VALUES
('Campus Admin', 'admin@uiu.ac.bd', '$2y$10$3TZsHsL03vh.7k8O8PdaE.MpZr2ma4XHlXseFipWYamjQWNkGJPx6', 'admin', 1, NULL, 'Campus Facilities Administration', '+880 1700-000000'),
('Fahim Ahmed', 'student@uiu.ac.bd', '$2y$10$3TZsHsL03vh.7k8O8PdaE.MpZr2ma4XHlXseFipWYamjQWNkGJPx6', 'student', 0, '011 221 045', 'Computer Science & Engineering', '+880 1712-345678'),
('Fahim Ahmed', 'fahim@uiu.ac.bd', '$2y$10$3TZsHsL03vh.7k8O8PdaE.MpZr2ma4XHlXseFipWYamjQWNkGJPx6', 'student', 0, '011 221 045', 'Computer Science & Engineering', '+880 1712-345678'),
('Dr. Hasan Sarwar', 'faculty@uiu.ac.bd', '$2y$10$3TZsHsL03vh.7k8O8PdaE.MpZr2ma4XHlXseFipWYamjQWNkGJPx6', 'faculty', 1, 'FAC-1002', 'Department of Computer Science', '+880 1711-223344');

-- ------------------------------------------------------------
-- 2. SEED VENUES
-- ------------------------------------------------------------
INSERT INTO venues (id, name, type, location, capacity, infrastructure, rating, status, image, thumbnail, about, review_count) VALUES
('electrical-lab-4', 'Electrical Lab 4', 'Lab', 'Main Campus, North Wing', 40, 'Normal', 4.5, 'Available',
 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=1000&h=600&fit=crop',
 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=400&h=280&fit=crop',
 'Electrical Lab 4 is a fully equipped lab space for circuit design, testing, and hands-on electrical engineering coursework. Located in the North Wing of the Main Campus, it offers individual workstations with power outlets and safety equipment for practical sessions.', 25),

('uiu-multi-purpose-hall', 'UIU Multipurpose Hall', 'Lecture Hall', 'Ground Floor', 250, 'Normal', 4.9, 'Available',
 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=1000&h=600&fit=crop',
 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=400&h=280&fit=crop',
 'The UIU Multipurpose Hall serves as the central hub for grand events, cultural performances, and major academic gatherings at the United International University permanent campus. Equipped with advanced sound systems and staging built for live performances and acoustics.', 42),

('uiu-design-lab', 'UIU Design Lab', 'Lab', 'Main Campus', 35, 'Normal', 4.6, 'Available',
 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1000&h=600&fit=crop',
 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=400&h=280&fit=crop',
 'UIU Design Lab is built for creative and technical design work, with computer workstations, drawing tablets, and 3D printing tools for prototyping student projects.', 25),

('media-class', 'Media Class', 'Seminar', '8th Floor', 20, 'Normal', 4.3, 'Booked',
 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1000&h=600&fit=crop',
 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&h=280&fit=crop',
 'Media Class is a small studio-style room for video production, podcast recording, and media coursework, equipped with cameras, microphones, and editing stations.', 7),

('uiu-innovation-hub', 'UIU Innovation Hub', 'Seminar', 'iRIIC, 10th Floor', 120, 'Advanced', 4.8, 'Available',
 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=1000&h=600&fit=crop',
 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=400&h=280&fit=crop',
 'The UIU Innovation Hub is a space for students to share ideas, work together, and develop new projects at United International University.', 38),

('uiu-hall-2', 'UIU Hall-2', 'Lecture Hall', '6th Floor', 150, 'Normal', 4.4, 'Booked',
 'https://images.unsplash.com/photo-1560439514-4e9645039924?w=1000&h=600&fit=crop',
 'https://images.unsplash.com/photo-1560439514-4e9645039924?w=400&h=280&fit=crop',
 'UIU Hall-2 is a large lecture hall on the 6th Floor, suitable for lectures, seminars, and mid-sized events, equipped with a sound system and stage lighting.', 30);

-- ------------------------------------------------------------
-- 3. SEED EQUIPMENT
-- ------------------------------------------------------------
INSERT INTO equipment (venue_id, item) VALUES
('electrical-lab-4', 'Power Outlets'), ('electrical-lab-4', 'Lab Computers'), ('electrical-lab-4', 'Tool Kits'), ('electrical-lab-4', 'Safety Gear'),
('uiu-multi-purpose-hall', '4K Projector'), ('uiu-multi-purpose-hall', 'Smart Whiteboard'), ('uiu-multi-purpose-hall', 'Spatial Audio'), ('uiu-multi-purpose-hall', 'Motorized Podium'),
('uiu-design-lab', 'Design Computers'), ('uiu-design-lab', 'Drawing Tablets'), ('uiu-design-lab', '3D Printer'), ('uiu-design-lab', 'Task Lighting'),
('media-class', 'Cameras'), ('media-class', 'Editing Suite'), ('media-class', 'Microphones'), ('media-class', 'Studio Lights'),
('uiu-innovation-hub', 'Projector'), ('uiu-innovation-hub', 'Smart Screens'), ('uiu-innovation-hub', 'Sound System'), ('uiu-innovation-hub', 'High-Speed WiFi'),
('uiu-hall-2', 'Microphones'), ('uiu-hall-2', 'Sound System'), ('uiu-hall-2', 'Projector'), ('uiu-hall-2', 'Stage Lighting');

-- ------------------------------------------------------------
-- 4. SEED SCHEDULE
-- ------------------------------------------------------------
INSERT INTO schedule (venue_id, event_date, title, time_range, host, status) VALUES
('electrical-lab-4', 'Aug 2', 'Circuits to Solutions', '09:00 AM – 11:30 AM', 'Mr. Md. Abid Hossain', 'confirmed'),
('electrical-lab-4', 'Aug 5', 'Learning to Experiment', '02:00 PM – 04:30 PM', 'Mr. Abdullah Ibne Masud Mahi', 'tentative'),
('uiu-multi-purpose-hall', 'Jul 26', 'Intro to Quantum Computing', '09:00 AM – 11:30 AM', 'Prof. Md. Mamun Elahi', 'confirmed'),
('uiu-multi-purpose-hall', 'Jul 27', 'Campus Life to Career', '02:00 PM – 04:30 PM', "Dean's Office", 'tentative'),
('uiu-design-lab', 'Aug 10', 'Concept to Creation', '09:00 AM – 11:30 AM', 'Prof. Md. Al-Sakib Khan Pathan', 'confirmed'),
('uiu-design-lab', 'Aug 11', 'Design to Development', '02:00 PM – 04:30 PM', "Dean's Office", 'tentative'),
('media-class', 'Aug 15', 'Media Skills to Career', '09:00 AM – 11:30 AM', 'Prof. Mario Hirstein', 'confirmed'),
('media-class', 'Aug 16', 'Learning to Broadcasting', '02:00 PM – 04:30 PM', 'Ms. Kazi Karun Nahar Tania', 'tentative'),
('uiu-innovation-hub', 'Jul 27', 'Technology to Entrepreneurship', '02:00 PM – 04:30 PM', "Dean's Office", 'tentative'),
('uiu-innovation-hub', 'Jul 30', 'Research to Real-World Solutions', '09:00 AM – 11:30 AM', 'Prof. Md. Mamun Elahi', 'confirmed'),
('uiu-hall-2', 'Aug 3', 'Learning to Leadership', '09:00 AM – 11:30 AM', 'Prof. Dr. Muhammad Nurul Huda', 'confirmed'),
('uiu-hall-2', 'Aug 4', 'Skills to Success', '02:00 PM – 04:30 PM', 'Prof. Dr. Khondokar Abdullah-Al-Mamun', 'tentative');

-- ------------------------------------------------------------
-- 5. SEED REVIEWS
-- ------------------------------------------------------------
INSERT INTO reviews (venue_id, initials, reviewer_name, time_ago, review_text) VALUES
('electrical-lab-4', 'FR', 'Mr. M. Fahmin Rahman', '4 days ago', 'Excellent lab facilities. The hands-on equipment made our electrical experiments smooth, practical, and engaging.'),
('uiu-multi-purpose-hall', 'HS', 'Dr. Hasan Sarwar', '2 days ago', 'Excellent facilities. The hybrid meeting tech worked flawlessly for my guest speaker in Berlin.'),
('uiu-design-lab', 'SH', 'Dr. Md. Shorab Hossain', '5 days ago', 'Creative and well-equipped space. The Design Lab provided the perfect environment to turn our ideas into engaging and innovative designs.'),
('media-class', 'UA', 'Ms. Umme Ammara', '2 days ago', 'Great creative environment. The media class had everything needed to bring our ideas to life and make every session engaging.'),
('uiu-innovation-hub', 'MN', 'Dr. Muhammad Nomani Kabir', '3 days ago', 'Great place for innovation and collaboration. The space made our workshop and project discussions smooth and engaging.'),
('uiu-hall-2', 'MI', 'Dr. Md. Motaharul Islam', '2 days ago', 'Spacious and comfortable venue. The setup was perfect for our seminar, making the event smooth and enjoyable for everyone.');

-- ------------------------------------------------------------
-- 6. SEED REQUESTS (Booking History & Initial Requests)
-- ------------------------------------------------------------
INSERT INTO requests (user_id, venue_name, venue_location, booking_date, booking_time, purpose, description, attachment, status) VALUES
(2, 'UIU Innovation Hub', 'iRIIC, 10th Floor', '2025-07-12', '02:00 PM – 04:00 PM', 'Club Seminar', 'Robotics and AI Club annual orientation seminar for newly admitted students.', NULL, 'Approved'),
(2, 'UIU Multipurpose Hall', 'Ground Floor', '2025-07-18', '10:00 AM – 04:00 PM', 'Tech Fest Exhibition', 'Grand campus tech fest project exhibition with interactive project showcase booths.', NULL, 'Pending'),
(2, 'UIU Multipurpose Hall', 'Ground Floor', '2025-07-19', '10:00 AM – 01:00 PM', 'Club Seminar', 'Photography club lighting and editing masterclass.', NULL, 'Pending'),
(2, 'UIU Design Lab', 'Main Campus', '2025-07-25', '11:30 AM – 01:30 PM', 'Workshop', 'Prototyping and 3D modeling workshop for engineering seniors.', NULL, 'Approved'),
(2, 'Electrical Lab 4', 'Main Campus, North Wing', '2025-07-30', '09:00 AM – 11:30 AM', 'Coursework', 'Departmental final lab practical exam.', NULL, 'Rejected');

-- ------------------------------------------------------------
-- 7. SEED MESSAGES
-- ------------------------------------------------------------
INSERT INTO messages (sender_id, receiver_id, sender_name, text, time_sent, is_read) VALUES
(1, 2, 'Admin - Campus Center', 'Hello Fahim! Your booking request for the Innovation Hub has been received and approved.', '10:38 AM', 1),
(2, 1, 'Fahim Ahmed', 'Hi! Yes, we are expecting around 25 students. We will need the projector and microphone set if possible.', '10:40 AM', 1),
(1, 2, 'Admin - Campus Center', 'Sure, the Innovation Hub is available and the projector is built-in. Multimedia access is enabled for your reservation.', '10:42 AM', 1),
(4, 1, 'Dr. Hasan Sarwar', 'Is there any lecture hall available for our senior project symposium next Wednesday?', 'Yesterday', 0);

-- ------------------------------------------------------------
-- 8. SEED NOTIFICATIONS
-- ------------------------------------------------------------
INSERT INTO notifications (user_id, title, message, type, time_ago, is_read) VALUES
(2, 'Campus-wide Maintenance', 'Main auditorium and GB4 will be closed from Sep 18 to Sep 21 for comprehensive audiovisual and stage lighting upgrades. Alternative halls are open for booking.', 'maintenance', '1 hour ago', 0),
(2, 'Your Booking Request is Accepted', 'Your reservation for UIU Innovation Hub on Jul 12, 2025 has been officially approved by Campus Administration. Hall keys can be collected at GB 101.', 'approval', '2 hours ago', 0),
(2, 'Request Rejected', 'The booking request for Electrical Lab 4 has been rejected due to a scheduled university departmental examination.', 'rejection', '3 hours ago', 1);
