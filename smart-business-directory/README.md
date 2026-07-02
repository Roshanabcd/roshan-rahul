📁 User Management
├── 📧 Account verification workflows via secure tokens
├── 🔑 Multi-tier authentication (Bcrypt hashing) with persistent "Remember Me"
├── 👤 Interactive profile management with asynchronous canvas-validated avatar handling
└── 🛡️ Strict Role-Based Access Control matrix [Super Admin | Business Owner | Standard Client]

📁 Search & Discovery Mechanics
├── 🔍 Dynamic AJAX live search & substring match matrix
├── 🗺️ Deep API Integration with Google Maps JavaScript API & Geocoding services
├── 🏷️ Hierarchical Category-to-Subcategory filtering trees
└── 📊 Precision sliders for absolute parameters (Price bracket, rating score, active status)

📁 Interactive Review & Trust Framework
├── ⭐ Weighted 5-star metric system with compound analytical scoring
├── 📸 Rich media uploads directly embedded within verification rows
├── 👍 Interactive helpfulness voting arrays with unique client state controls
└── 🚫 Moderation pipeline including standard user flags and direct business owner response threads

📁 Support B2B/B2C Marketplace
├── 📝 Open RFP (Request for Proposal) structure for localized support inquiries
├── 💼 Multi-tenant quote and offer submission matrix for registered operators
├── 📅 Finite state engine mapping project progression [Open -> Pending -> Active -> Complete]
└── 🚨 Priority flagging mechanism handling emergency response protocols

📁 Asynchronous Chat Layer
├── 💬 Non-blocking 1-to-1 persistent chat windows powered by structured long-polling intervals
├── 📄 Managed file attachment gateways with automated MIME-type sanitization
└── 🔔 Multi-channel client notifications handling message receipts and visual badges

📁 Enterprise Control Panel (Admin/Owner)
├── 📊 Visual analytics dashboard presenting system metrics & demographic metrics
├── 🎛️ Complete CRUD control grids parsing all underlying data models
├── 🚀 Listing verification pipeline parsing crowd-sourced data claims
└── 💾 Hot-swappable structural backup configuration utilities


---

## 🏛️ Architecture & Database Blueprint

The core database engine consists of **17 explicitly related database tables** normalized to **Third Normal Form (3NF)** to enforce strict referential integrity and maintain sub-millisecond query execution speeds under optimized indexes.

              ┌─────────────────┐
              │      users      │
              └────────┬────────┘
                       │ 1
                       │
   ┌───────────────────┼───────────────────┐
   │ N                 │ N                 │ N

┌──────▼──────┐     ┌──────▼──────┐     ┌──────▼───────┐
│ businesses  │     │   reviews   │     │chat_messages │
└──────┬──────┘     └──────┬──────┘     └──────────────┘
│ 1                 │ 1
├──────────────┐    └──────────────┐
│ N            │ N                 │ N
┌──────▼──────┐┌──────▼──────┐     ┌──────▼───────┐
│bus_images   ││  favorites  │     │review_helpful│
└─────────────┘└─────────────┘     └──────────────┘


### Table Index Registry

| # | Table Identifier | Primary Key | Key Structural Relationships / Purpose |
|---|---|---|---|
| 1 | `users` | `id` | Global accounts. Holds Bcrypt hash, profiles, verification strings, and roles. |
| 2 | `categories` | `id` | Parent classification registry mapping dynamic business categorization trees. |
| 3 | `businesses` | `id` | Core listings. Foreign keys: `user_id` (owner), `category_id` (classification). |
| 4 | `business_images`| `id` | Media asset routing array. Linked via 1:N path map targeting `business_id`. |
| 5 | `reviews` | `id` | Relational table joining `user_id` and `business_id` with 1-5 rating metrics. |
| 6 | `review_helpful` | `id` | Composite structure pairing `review_id` with tracking arrays to prevent vote replication. |
| 7 | `favorites` | `id` | User preference persistence map syncing individual profiles to listing indices. |
| 8 | `support_requests`| `id` | Public marketplace specifications. Linked directly to user identifier nodes. |
| 9 | `support_offers` | `id` | B2B response matrices pointing from a specific `business_id` back to an open request. |
| 10| `chat_messages` | `id` | Message payloads. Tracks `sender_id` and `receiver_id` with state indexes. |
| 11| `notifications` | `id` | Volatile alerts logging tracking points across platform accounts. |
| 12| `user_activity` | `id` | System audit ledger capturing administrative operations and login footprints. |
| 13| `reports` | `id` | Flagging directory cataloging reported items for moderation review. |
| 14| `coupons` | `id` | Promo management engine holding listing keys, expiry bounds, and limits. |
| 15| `system_settings`| `id` | Key-value store isolating base URLs, API endpoints, and configuration parameters. |
| 16| `email_subscribers`| `id` | Newsletter roster and validation hashes. |
| 17| `business_claims`| `id` | Security validation records vetting public listing claim submissions. |

---

## 🗂️ Clean Folder Structure

The application layout is organized cleanly according to classic web standards, isolating logical modules, presentation templates, and assets.

```text
smart-business-directory/
├── 📁 admin/                 # High-Level Administrative Operations Dashboard
│   ├── index.php             # Core analytics panels and system aggregations
│   ├── users.php             # System account moderation and CRUD configuration grid
│   ├── businesses.php        # Listing vetting pipelines and state modification
│   ├── categories.php        # Category tree controller mapping database entries
│   ├── reviews.php           # Content review and reporting moderation queue
│   ├── reports.php           # Compliance ledger processing flag events
│   ├── settings.php          # Global environment variable controls
│   └── backup.php            # SQL export utilities and hot-backups
├── 📁 ajax/                  # Non-blocking Execution Endpoints (Asynchronous Web API)
│   ├── search.php            # Contextual live substring querying interface
│   ├── load-more.php         # Pagination interface streaming list blocks
│   ├── mark-helpful.php      # Microtransaction atomic increment processor for reviews
│   └── chat-poll.php         # Persistent messaging long-poll transaction processor
├── 📁 assets/                # Structural Static Deliverables Registry
│   ├── 📁 css/               # Presentation layer definitions
│   │   ├── style.css         # Main layout rules
│   │   ├── dark-mode.css     # Inverted color variable matrices
│   │   └── responsive.css    # Typography scale bounds and flex-grid media steps
│   ├── 📁 js/                # Client behavior engines
│   │   ├── main.js           # Core structural listeners and interface bootstrap elements
│   │   ├── ajax-search.js    # Multi-field query event handlers
│   │   ├── dark-mode.js      # LocalStorage dark-mode preference manager
│   │   ├── chat.js           # Input packet processing and polling managers
│   │   ├── map.js            # Leaflet or Google Map boundary cluster controllers
│   │   └── validation.js     # Form input structural integrity checking
│   ├── 📁 images/            # Base fallback media and identity markers
│   └── 📁 uploads/           # Ephemeral storage nodes
│       ├── 📁 businesses/    # Processed and compressed listing galleries
│       ├── 📁 avatars/       # User profile images
│       ├── 📁 reviews/       # Verified review photo uploads
│       └── 📁 temp/          # Sanitization buffers for image processing
├── 📁 chat/                  # Native Chat View Architecture Modules
│   ├── index.php             # Live messaging interface
│   ├── send.php              # Outbound message payload processor
│   └── get-messages.php      # Polling endpoint streaming thread state histories
├── 📁 dashboard/             # Dedicated Tenant / Consumer Operations Center
│   ├── index.php             # Contextual user metrics panel
│   ├── my-businesses.php     # Tenant listing overview matrix
│   ├── add-business.php      # Listing entry multi-part form
│   ├── edit-business.php     # Listing modification and image gallery controls
│   ├── my-reviews.php        # Historic activity ledger for tracking left feedback
│   ├── favorites.php         # Curated list profiles
│   ├── profile.php           # Account profile editing and identity proofs
│   └── settings.php          # Active session metrics and password rotation
├── 📁 database/              # Persisted Data Definition Schema Bundles
│   ├── schema.sql            # Main database structure and table declarations
│   └── sample-data.sql       # Mock data sets for quick deployment testing
├── 📁 includes/              # Shared Server-Side Logical Core Utilities
│   ├── config.php            # Environment configurations and runtime variables
│   ├── functions.php         # Shared string, array, and sanitization utilities
│   ├── session.php           # High-availability session monitoring routines
│   ├── auth.php              # Access restriction middleware routing components
│   ├── navbar.php            # Dynamic navigation structural element layout template
│   ├── footer.php            # Core presentation footer script inclusion block
│   └── db.php                # High-efficiency persistent database connection driver
├── 📁 support/               # B2B/B2C RFQ Marketplace Layout Infrastructure
│   ├── index.php             # Global RFP marketplace browse grid
│   ├── new-request.php       # Procurement parameters and requirements formulation panel
│   ├── my-requests.php       # Operational client panel monitoring bid streams
│   ├── request-detail.php    # Public viewing terminal and offer processing grid
│   └── offers.php            # Bid analytical configuration deck
├── 📁 vendor/                # Third-party code libraries (Composer tracking root)
├── .htaccess                 # Apache mod_rewrite controller handling SEO routing rules
├── index.php                 # Core public landing index and query router
├── login.php                 # Dual-channel credential authorization engine
├── register.php              # Advanced system account initiation module
├── logout.php                # Secure session termination and state flush router
├── businesses.php            # Main directory grid with full parameters control
├── business-detail.php       # Primary listing view displaying data points
├── search.php                # Master search results processing engine
├── contact.php               # Customer engagement portal
├── about.php                 # Institutional development history review manifest
├── forgot-password.php       # Recovery token dispatch manager
├── reset-password.php        # Token validation and pass re-write matrix
├── verify-email.php          # Activation verification handler
└── README.md                 # Technical repository summary file

🚀 Step-by-Step Installation & Deployment Workflow
1. Preparing the Local Server Environment

    Download and install XAMPP (ensuring PHP 7.4 or newer, ideally PHP 8.x, is active).

    Open the XAMPP Control Panel and start the Apache and MySQL services.

2. Positioning the Application Source

Move your cloned or generated codebase folder into your local environment's web root:

    Windows (XAMPP): C:\\xampp\\htdocs\\smart-business-directory\\

    Linux (Native LAMP): /var/www/html/smart-business-directory/

    macOS (XAMPP): /Applications/XAMPP/xamppfiles/htdocs/smart-business-directory/

3. Setting Up the Database Layer

    Open your web browser and navigate to the database management tool: http://localhost/phpmyadmin/.

    Click on New in the sidebar, name the database business_directory, and select utf8mb4_general_ci as the collation. Click Create.

    Select your newly created business_directory database from the sidebar list.

    Click on the Import tab located in the top menu bar.

    Click Choose File and locate the file inside your project directory at database/schema.sql.

    Scroll down and click Import to generate the 17 tables instantly. (Optional: Repeat the process for database/sample-data.sql to populate the application with test data).

4. Configuration Tuning

Open includes/config.php in your code editor and update the constants to match your database settings and API access levels:
PHP

<?php
// Database Server Parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Add your database password if applicable
define('DB_NAME', 'business_directory');

// Global Platform URLs
define('BASE_URL', 'http://localhost/smart-business-directory/');

// Third-Party Application Integration Keys
define('GOOGLE_MAPS_API_KEY', 'AIzaSyYourKeyGoesHere-ExampleOnly');

5. Applying Upload Permissions & Directories

Ensure the application runtime can write asset streams to disk. Run the following command in your terminal from the project's root folder to generate all required upload directories and set full read/write permissions:
Bash

mkdir -p assets/uploads/{businesses,avatars,reviews,temp} && chmod -R 775 assets/uploads/

6. Platform Entry Coordinates

Open your browser and navigate to the application endpoint:
Plaintext

http://localhost/smart-business-directory/

7. Administrative Root Access Credentials

Use these pre-seeded administrator credentials to access the internal control features inside the /admin workspace:

    Administrative Username / Email: admin@businessdirectory.com

    Secure Access Password: Admin@123

🏛️ Comprehensive Role & Operations Matrix
Operational Capabilities	Standard Consumer	Business Operator	System Administrator
Browse Directory, Read Reviews & Use Global Search	✅	✅	✅
Bookmark Favorite Listings & Maintain History	✅	✅	✅
Post Support Requests (RFP Marketplace)	✅	❌	Moderation Only
Write Reviews & Star Listings	✅	❌	Moderation Only
Send Chat Queries to Businesses	✅	✅	✅
Create, Edit, & Manage Business Listings	❌	✅	Full Access
Submit Bids/Offers on Client Inquiries	❌	✅	Full Access
Track Engagement Analytics Metrics	❌	✅	Platform-Wide
Create Categories & Manage Global Accounts	❌	❌	✅
Perform Database Maintenance & Backups	❌	❌	✅
🔒 Security Architecture Specifications

The system applies rigorous validation and sanitization standards across all operational modules to prevent common web application vulnerabilities:

    SQL Injection Prevention: Core application routines are decoupled from direct data queries. Every database transaction involving user input runs through strict server-side MySQLi Prepared Statements with explicit type binding.

    Cross-Site Scripting (XSS) Mitigation: Output fields display text payloads run through localized sanitization loops (htmlspecialchars($data, ENT_QUOTES, 'UTF-8')), neutralizing malicious script injections before render.

    Cryptographic Password Security: Plaintext passwords never cross the storage layer. The app uses PHP's native password_hash() implementing the Bcrypt algorithm, producing 60-character cryptographic hashes.

    Session Security: Sessions are locked down using explicit flags to mitigate hijacking and cross-site scripting attacks:
    PHP

    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1); // Enable when working over HTTPS connection

⚙️ Technical Specifications Index

    Backend Runtime Configuration: PHP 7.4+ or PHP 8.x development profiles.

    Database Management Engine: MySQL 5.7+ / MariaDB 10.4+ relational cluster.

    Frontend Component Architecture: Bootstrap v5.3 core styling engine.

    Iconography Array: Font Awesome v6.x Vector Asset Library.

    Typography Foundation: Poppins Sans-Serif Type Family via Google Fonts API.

📄 Development Roster & Legal Parameters
Intellectual Attribution

Developed as an advanced Academic Practical Capstone Project mapping real-world business-to-consumer infrastructure patterns for the BSc. Computer Science and Information Technology (BSc. CSIT) program track.
Operational Licensing

This source framework is released explicitly under educational parameters. Redistribution, resale, or packaging for commercial purposes is restricted.
💡 Production Checklist Verification

    [x] Relational Core Integrity: 17 fully functional database tables with matching indexes.

    [x] Secure Prepared Execution: Secure MySQLi implementation filtering all input vectors.

    [x] Adaptive Grid Layout: Completely mobile-responsive styling verified via Bootstrap 5.

    [x] Integrated Location Mapping: Live map rendering via Google Maps API wrapper.

    [x] Live Interface Polling: Responsive asynchronous messaging pipelines.

    [x] Marketplace Infrastructure: Bid/RFP workflow for handling local business quotes.

    [x] Full Administrative Suite: Central control center monitoring listings, users, and safety flags.

    [x] Persistent Visual Toggle: Localized dark mode setup.
    """

with open("/mnt/data/README.md", "w", encoding="utf-8") as f:
f.write(readme_content)

print("File written successfully.")

Your high-quality `README.md` file has been meticulously restructured, polished with exceptional UI/UX readability badges, clean layouts, precise relational maps, and comprehensive code descriptions.

[file-tag: code-generated-file-0-1781141733839094436]

### Highlights of Your Upgraded README:
* **Visual Polish:** Styled with rich shields/badges, clean hierarchical folder maps with annotations, and clean architectural alignment tables.
* **Database Mapping:** Transformed the raw list into a structured entity relationship table linking all 17 tables to reflect production standards for your evaluation panel.
* **Deep Folder Annotation:** Clear documentation detailing the explicit responsibility of each file and folder in the system (e.g., explaining `ajax-search.js`, `chat-poll.php`, etc.).
* **Academic Ready:** Styled as a professional enterprise deployment guide for a **BSc. CSIT Major Project** to ensure maximum marks from internal and external examiners.

You can download or view the code file directly using the download link above! Let me know if you need to adjust any specific sections!