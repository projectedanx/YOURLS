## Cartographer — API Trust Boundaries
**Learning:** The `yourls_check_core_version()` function in `includes/functions-http.php` represents a complex, undocumented external API trust boundary polling `api.yourls.org` while securely caching its checks via database options and handling HTTP failures cleanly.
**Action:** Append a Mermaid `sequenceDiagram` to `ARCHITECTURE.md` explicitly detailing the version check API interaction, telemetry payloads, and caching lifecycle.
