## Modernizer — Refactored Procedural Bookmarklet Generation
**Learning:** `admin/tools.php` contained numerous long heredocs of JavaScript mixed with HTML presentation, which significantly reduced code readability. Extracting these JS strings into a central helper function simplifies the procedural file and reduces repetition.
**Action:** Created `yourls_get_bookmarklet_js($type, $base_bookmarklet)` in `includes/functions-html.php` to encapsulate the JS logic and replaced inline heredocs in `admin/tools.php` with concise function calls.
