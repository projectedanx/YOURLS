## Cartographer — Authentication and Routing Topography
**Learning:** Evaluated the authentication logic in `functions-auth.php` mapping out how YOURLS handles fallback checks (Cookie -> HTTP Auth -> POST credentials). Also mapped the request intercept and URL scheme evaluation paths in `yourls-loader.php`. When testing the CI configuration, ensure that custom `yut_TestCase` structures aren't mistakenly removed or modified during cleanup passes as they are critical to the project's PHPUnit 10 test execution.
**Action:** When acting as Cartographer, strictly append visual syntax into `.md` files without applying code style fixes or cleanup steps to `.php` files, even in tests.

## Cartographer — URL Shortening Core Flow
**Learning:** Evaluated the `yourls_add_new_link` core flow in `includes/functions-shorturls.php` mapping the sequence of hook shunts, duplicate URL checks, keyword generation loops, and database insertion, including concurrency exception handling.
**Action:** Always capture branching logic and edge cases (e.g., concurrency, validation failures) when creating `sequenceDiagram` to accurately reflect system resilience and error states.
