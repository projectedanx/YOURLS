## Cartographer — API Trust Boundaries & System Installation Pipeline
**Learning:** `yourls_check_core_version()` handles external API checks and caching, while `admin/install.php` delegates environment checks and database initialization to `includes/functions-install.php`. Both pipelines were previously mapped in `ARCHITECTURE.md`.
**Action:** Maintain these complex boundaries and setup processes explicitly in the Mermaid diagram maps.

## Cartographer — Database Schema (Relational Topology)
**Learning:** The `YOURLS_URL`, `YOURLS_OPTIONS`, and `YOURLS_LOG` tables are instantiated in `includes/functions-install.php` and their schemas imply a relational connection (`keyword` acting as the PK in `YOURLS_URL` and referenced by `shorturl` in `YOURLS_LOG`).
**Action:** Map the core YOURLS database tables and their foreign key relationships using a Mermaid `erDiagram` to explicitly visualize the schema structures.

## Cartographer — API Surface Map Boundaries
**Learning:** `yourls-api.php` acts as a single-point router, dispatching requests based on the `action` parameter. The surface contract is now fully detailed using the AXIOM v1.0 standard in an OpenAPI 3.1 blueprint (`docs/api-spec.yaml`), aligning the codebase with its external facing schema representation.
**Action:** Implemented the OpenAPI specification artifact to document the mereological boundaries of the API.
