## Cartographer — API Trust Boundaries
**Learning:** The `yourls_check_core_version()` function in `includes/functions-http.php` represents a complex, undocumented external API trust boundary polling `api.yourls.org` while securely caching its checks via database options and handling HTTP failures cleanly.
**Action:** Append a Mermaid `sequenceDiagram` to `ARCHITECTURE.md` explicitly detailing the version check API interaction, telemetry payloads, and caching lifecycle.

## Cartographer — System Installation Pipeline
**Learning:** The YOURLS installation flow in `admin/install.php` delegates environment and dependency checks (PDO, PHP version, DB version) to `includes/functions-install.php`, subsequently provisioning the URL, OPTIONS, and LOG tables and initializing state with sample data upon passing pre-requisites.
**Action:** Append a Mermaid `sequenceDiagram` to `ARCHITECTURE.md` explicitly mapping the `admin/install.php` setup process, environment validations, filesystem interactions (like `.htaccess` creation), and database bootstrap flow.