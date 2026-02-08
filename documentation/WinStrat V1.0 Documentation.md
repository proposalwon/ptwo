WinStrat Project Master Documentation
Project Version: 1.0 (Modular SaaS Transition)

System Type: Multi-Tenant GovCon Capture & Intelligence Suite

1. Conversation Summary
The project has transitioned from a monolithic prototype to a professional, scalable SaaS architecture. The core logic centers on a "Global Lake vs. Private Bucket" model. Users search a master repository of 400,000+ government research records and "promote" selected leads into their private, editable tenant pipeline. This ensures data integrity for the master lake while allowing full customization for the end user.

2. Technical Stack & Standards
Core Technologies
Backend: PHP 7.2+ (Stateless API approach).

Database: MariaDB/MySQL (InnoDB).

Frontend: Vanilla JavaScript (ES Modules), HTML5, CSS3.

Search Engine: Database-level FULLTEXT indexing on title, agency, and description fields.

Coding Standards
Tenancy Isolation: Every database query must include a tenant_id filter to prevent cross-client data leakage.

Modular JS: All feature-specific logic must be housed in js/modules/ and lazy-loaded via the route() function in app.js.

API Security: All endpoints in /api/ must verify session status and user permissions before execution.

3. Directory Structure
Plaintext
/ptwo/
├── api/                # Stateless JSON Endpoints
│   ├── admin/          # Platform-level management (Onboarding, Global Ingest)
│   ├── capture/        # Tenant-level Research & Pipeline logic
│   └── auth.php        # Session & Gatekeeper logic
├── js/
│   ├── modules/        # Lazy-loaded ES Modules (Encapsulated logic)
│   │   ├── admin.js    # Platform stats & client onboarding
│   │   ├── capture.js  # Lake search & pipeline management
│   │   └── intel.js    # Competitive analysis logic
│   └── app.js          # Core Bootstrapper & dynamic router
├── src/                # Backend Core (Shared logic)
│   ├── db.php          # PDO Connection & Session security config
│   └── functions.php   # Global helpers (Audit logs, Tenancy filters)
├── css/
│   └── ptwo.css        # Global CSS variables & UI components
└── index.php           # The SPA Shell (Session load & DOM structure)

4. Database Schema Overview
Global Research Lake (research_records)
The master repository containing over 400,000 records from SAM.gov and Texas ESBD.

Key Fields: title, solicitation_number, agency_name, description, award_amount, awardee_name.

Optimization: FULLTEXT index on title, agency_name, and description.

Private Pipeline (tenant_opportunities)
Mirrors the research lake structure but is unique per tenant.

Added Fields: tenant_id, p_win, internal_status, assigned_user, internal_notes.

User Management (users)
is_platform_admin: Boolean flag granting access to the Platform Admin module.

home_module: User preference for the default landing page (e.g., 'capture' or 'admin').

5. Developer Onboarding
Environment: Set up a PHP/MySQL environment (XAMPP/WAMP or Linux).

Database: Import the research_records.sql and the core schema.

User Setup: Set a user's is_platform_admin to 1 in the database to enable the Admin navigation link.

Routing: To add a new module, create the view div in index.php, add the nav link, and create the corresponding JS module in js/modules/.

6. Project Roadmap
Phase 1: Data Integration (Current)
[x] Establish Multi-tenant session management.

[x] Implement Modular JS routing.

[x] Define Left-aligned Navigation UI.

[ ] Next Task: Connect api/capture/search_lake.php to the frontend searchLake() function.

Phase 2: Promotion & Customization
[ ] Build the "Promotion" API to copy global records to tenant buckets.

[ ] Build the "Platform Admin" dashboard to show global record health (400k+ stats).

Phase 3: Advanced Intelligence
[ ] Agency Intel: Aggregate award_amount data by agency_name.

[ ] Competitive Tracking: Profile awardees using the awardee_name history in the lake.