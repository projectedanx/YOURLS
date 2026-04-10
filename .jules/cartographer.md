## Cartographer — API Trust Boundaries & System Installation Pipeline (Pruned)
**Learning:** `yourls_check_core_version()` handles external API checks and caching, while `admin/install.php` delegates environment checks and database initialization to `includes/functions-install.php`. Both pipelines were previously mapped in `ARCHITECTURE.md`.
**Action:** Maintain these complex boundaries and setup processes explicitly in the Mermaid diagram maps.

## Cartographer — Database Schema (Relational Topology)
**Learning:** The `YOURLS_URL`, `YOURLS_OPTIONS`, and `YOURLS_LOG` tables are instantiated in `includes/functions-install.php` and their schemas imply a relational connection (`keyword` acting as the PK in `YOURLS_URL` and referenced by `shorturl` in `YOURLS_LOG`).
**Action:** Map the core YOURLS database tables and their foreign key relationships using a Mermaid `erDiagram` to explicitly visualize the schema structures.
