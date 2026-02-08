Here is the comprehensive documentation for the WinStrat Capture & Pipeline System, formatted in Markdown for your project repository or developer hand-off.

WinStrat: Technical Specification & Hand-off Guide
System Version: 1.1 (SaaS Scalability Refactor)

Architecture: Single-Page Interface (SPA) / PHP PDO / ES6 Modules

1. Executive Summary
WinStrat is a multi-tenant SaaS platform for government contractors. The system separates high-volume global market data (Research Lake) from private, user-specific sales workflows (Opportunities Pipeline). The recent refactor standardizes data transport via FormData and enforces strict decoupling of UI and Logic to ensure 1:100+ user scalability.

2. Database Schema (Master)
A. Global Data: research_lake
A read-only repository containing ~400,000+ solicitation records. | Column | Type | Description | | :--- | :--- | :--- | | id | INT (PK) | Master record identifier. | | solicitation_number | VARCHAR | Government ID for the contract. | | title | VARCHAR | Project name. | | description | TEXT | Scope of work. | | agency_name | VARCHAR | Issuing government body. |

Indexing: FULLTEXT(title, agency_name, description) using MariaDB/MySQL engine.

B. Tenant Data: tenant_opportunities
Private workspace for individual companies. | Column | Type | Description | | :--- | :--- | :--- | | tenant_id | INT | FK to tenants table; ensures data isolation. | | lake_ref_id | INT | Links back to the master record in research_lake. | | status | ENUM | Qualified, Proposal, Submitted, Won, Lost. |

C. SaaS Limits: tenants
Tracks subscription usage.

plan_promotion_limit: Integer cap on monthly leads.

current_month_promotions: Current usage counter (reset monthly).

3. Frontend Standards
3.1 SPA Shell Architecture
index.php serves as the application shell. Sections are toggled via the hidden attribute or display: none rather than page reloads.

3.2 Event Delegation (The "No Onclick" Rule)
To comply with modern Security Policies (CSP) and optimize memory, no inline onclick attributes are permitted.

Method: Listeners are attached to parent containers (#lake-results, #pipeline-list).

Logic: The event listener checks e.target.matches('.btn-promote') to trigger actions.

3.3 Data Transport (FormData)
All POST operations (Promote, Update Status, Delete) must use the FormData API.

Why: Automatically handles encoding, eliminates encodeURIComponent boilerplate, and is required for future file upload (PDF proposal) support.

4. API Endpoints & Business Logic
api/capture/search_lake.php
Mode: GET

Standard: Uses BOOLEAN MODE for Full-Text Search.

Constraint: Minimum 3-character query length.

api/capture/promote.php
Mode: POST (requires lake_id)

Logic: Executes a Database Transaction.

Lock tenant row (FOR UPDATE).

Check if current_month_promotions < plan_promotion_limit.

Copy record from Lake to tenant_opportunities.

Increment usage counter.

COMMIT (or ROLLBACK on error).

api/pipeline/update_status.php
Mode: POST (requires id, status)

Security: Query must be WHERE id = :id AND tenant_id = :tenant_id to prevent Cross-Tenant data manipulation.

5. Directory Structure
Plaintext
/ptwo
├── api/
│   ├── capture/
│   │   ├── search_lake.php
│   │   └── promote.php
│   └── pipeline/
│       ├── get_opportunities.php
│       └── update_status.php
├── js/
│   ├── app.js (Main Router)
│   └── modules/
│       ├── capture.js (Search/Promote logic)
│       └── pipeline.js (View/Edit logic)
├── src/
│   └── db.php (PDO Connection)
└── index.php (SPA Shell)
6. Validation Checklist for Developers
Isolation: Does every Pipeline query include a tenant_id filter?

Transactions: Does the promotion script use beginTransaction()?

Transport: Are all POST requests using FormData?

UI: Are all events attached via addEventListener in the modules?

Search: Does the Search API bypass the 50% rule using BOOLEAN MODE?

WinStrat Development Team | Confidential Technical Specification