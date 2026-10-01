/* ================================
   VenueVibe - venues-data.js
   ================================
   THE SINGLE SOURCE OF TRUTH for every venue.

   This used to be spread across 6 separate pairs of
   html/css/js files (one per venue) - which meant adding a
   7th venue meant copy-pasting a whole new set of files.

   Now there is ONE shared "venue-details.html" page, and it
   just looks up the venue by "id" in this array. To add a
   new venue, add a new object below - no new HTML/CSS/JS
   files needed.
*/

var VENUES = [
    {
        id: "electrical-lab-4",
        name: "Electrical Lab 4",
        type: "Lab",
        location: "Main Campus, North Wing",
        capacity: 40,
        infrastructure: "Normal",
        rating: 4.5,
        status: "Available",
        image: "https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=1000&h=600&fit=crop",
        thumbnail: "https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=400&h=280&fit=crop",
        equipment: ["Power Outlets", "Lab Computers", "Tool Kits", "Safety Gear"],
        about: "Electrical Lab 4 is a fully equipped lab space for circuit design, testing, and hands-on electrical engineering coursework. Located in the North Wing of the Main Campus, it offers individual workstations with power outlets and safety equipment for practical sessions.",
        schedule: [
            { date: "Aug 2", title: "Circuits to Solutions", time: "09:00 AM – 11:30 AM", host: "Mr. Md. Abid Hossain", status: "confirmed" },
            { date: "Aug 5", title: "Learning to Experiment", time: "02:00 PM – 04:30 PM", host: "Mr. Abdullah Ibne Masud Mahi", status: "tentative" }
        ],
        review: { initials: "FR", name: "Mr. M. Fahmin Rahman", time: "4 days ago", text: "Excellent lab facilities. The hands-on equipment made our electrical experiments smooth, practical, and engaging." },
        reviewCount: 25
    },
    {
        id: "uiu-multi-purpose-hall",
        name: "UIU Multipurpose Hall",
        type: "Lecture Hall",
        location: "Ground Floor",
        capacity: 250,
        infrastructure: "Normal",
        rating: 4.9,
        status: "Available",
        image: "https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=1000&h=600&fit=crop",
        thumbnail: "https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=400&h=280&fit=crop",
        equipment: ["4K Projector", "Smart Whiteboard", "Spatial Audio", "Motorized Podium"],
        about: "The UIU Multipurpose Hall serves as the central hub for grand events, cultural performances, and major academic gatherings at the United International University permanent campus. Equipped with advanced sound systems and staging built for live performances and acoustics. Features professional stage and ambient lighting designed for theatrical dramas and concerts.",
        schedule: [
            { date: "Jul 26", title: "Intro to Quantum Computing", time: "09:00 AM – 11:30 AM", host: "Prof. Md. Mamun Elahi", status: "confirmed" },
            { date: "Jul 27", title: "Campus Life to Career", time: "02:00 PM – 04:30 PM", host: "Dean's Office", status: "tentative" }
        ],
        review: { initials: "HS", name: "Dr. Hasan Sarwar", time: "2 days ago", text: "Excellent facilities. The hybrid meeting tech worked flawlessly for my guest speaker in Berlin." },
        reviewCount: 42
    },
    {
        id: "uiu-design-lab",
        name: "UIU Design Lab",
        type: "Lab",
        location: "Main Campus",
        capacity: 35,
        infrastructure: "Normal",
        rating: 4.6,
        status: "Available",
        image: "https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1000&h=600&fit=crop",
        thumbnail: "https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=400&h=280&fit=crop",
        equipment: ["Design Computers", "Drawing Tablets", "3D Printer", "Task Lighting"],
        about: "UIU Design Lab is built for creative and technical design work, with computer workstations, drawing tablets, and 3D printing tools for prototyping student projects.",
        schedule: [
            { date: "Aug 10", title: "Concept to Creation", time: "09:00 AM – 11:30 AM", host: "Prof. Md. Al-Sakib Khan Pathan", status: "confirmed" },
            { date: "Aug 11", title: "Design to Development", time: "02:00 PM – 04:30 PM", host: "Dean's Office", status: "tentative" }
        ],
        review: { initials: "SH", name: "Dr. Md. Shorab Hossain", time: "5 days ago", text: "Creative and well-equipped space. The Design Lab provided the perfect environment to turn our ideas into engaging and innovative designs." },
        reviewCount: 25
    },
    {
        id: "media-class",
        name: "Media Class",
        type: "Seminar",
        location: "8th Floor",
        capacity: 20,
        infrastructure: "Normal",
        rating: 4.3,
        status: "Booked",
        image: "https://images.unsplash.com/photo-1497366216548-37526070297c?w=1000&h=600&fit=crop",
        thumbnail: "https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&h=280&fit=crop",
        equipment: ["Cameras", "Editing Suite", "Microphones", "Studio Lights"],
        about: "Media Class is a small studio-style room for video production, podcast recording, and media coursework, equipped with cameras, microphones, and editing stations.",
        schedule: [
            { date: "Aug 15", title: "Media Skills to Career", time: "09:00 AM – 11:30 AM", host: "Prof. Mario Hirstein", status: "confirmed" },
            { date: "Aug 16", title: "Learning to Broadcasting", time: "02:00 PM – 04:30 PM", host: "Ms. Kazi Karun Nahar Tania", status: "tentative" }
        ],
        review: { initials: "UA", name: "Ms. Umme Ammara", time: "2 days ago", text: "Great creative environment. The media class had everything needed to bring our ideas to life and make every session engaging." },
        reviewCount: 7
    },
    {
        id: "uiu-innovation-hub",
        name: "UIU Innovation Hub",
        type: "Seminar",
        location: "iRIIC, 10th Floor",
        capacity: 120,
        infrastructure: "Advanced",
        rating: 4.8,
        status: "Available",
        image: "https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=1000&h=600&fit=crop",
        thumbnail: "https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=400&h=280&fit=crop",
        equipment: ["Projector", "Smart Screens", "Sound System", "High-Speed WiFi"],
        about: "The UIU Innovation Hub is a space for students to share ideas, work together, and develop new projects at United International University. It helps students explore new technologies and turn their creative ideas into useful solutions. The hub also provides a friendly environment where students can learn, collaborate, and take part in different innovation activities.",
        schedule: [
            { date: "Jul 27", title: "Technology to Entrepreneurship", time: "02:00 PM – 04:30 PM", host: "Dean's Office", status: "tentative" },
            { date: "Jul 30", title: "Research to Real-World Solutions", time: "09:00 AM – 11:30 AM", host: "Prof. Md. Mamun Elahi", status: "confirmed" }
        ],
        review: { initials: "MN", name: "Dr. Muhammad Nomani Kabir", time: "3 days ago", text: "Great place for innovation and collaboration. The space made our workshop and project discussions smooth and engaging." },
        reviewCount: 38
    },
    {
        id: "uiu-hall-2",
        name: "UIU Hall-2",
        type: "Lecture Hall",
        location: "6th Floor",
        capacity: 150,
        infrastructure: "Normal",
        rating: 4.4,
        status: "Booked",
        image: "https://images.unsplash.com/photo-1560439514-4e9645039924?w=1000&h=600&fit=crop",
        thumbnail: "https://images.unsplash.com/photo-1560439514-4e9645039924?w=400&h=280&fit=crop",
        equipment: ["Microphones", "Sound System", "Projector", "Stage Lighting"],
        about: "UIU Hall-2 is a large lecture hall on the 6th Floor, suitable for lectures, seminars, and mid-sized events, equipped with a sound system and stage lighting.",
        schedule: [
            { date: "Aug 3", title: "Learning to Leadership", time: "09:00 AM – 11:30 AM", host: "Prof. Dr. Muhammad Nurul Huda", status: "confirmed" },
            { date: "Aug 4", title: "Skills to Success", time: "02:00 PM – 04:30 PM", host: "Prof. Dr. Khondokar Abdullah-Al-Mamun", status: "tentative" }
        ],
        review: { initials: "MI", name: "Dr. Md. Motaharul Islam", time: "2 days ago", text: "Spacious and comfortable venue. The setup was perfect for our seminar, making the event smooth and enjoyable for everyone." },
        reviewCount: 30
    }
];

// Small helper used by venue-list.html and venue-details.html
function getVenueById(id) {
    for (var i = 0; i < VENUES.length; i++) {
        if (VENUES[i].id === id) {
            return VENUES[i];
        }
    }
    return null;
}
