# 0xCARTO BLUEPRINT

## TIER 1: Repository Identity & Ontological Glossary
This repository houses YOURLS (Your Own URL Shortener), a set of PHP scripts that will allow you to run your own URL shortening service. It is designed to empower users with full control over their data, providing a self-hosted alternative to third-party services.
- **Pluriversal URL Resolution**: YOURLS supports multiple plugins and filters to customize the URL resolution process.
- **Golden Scars**: The codebase contains legacy procedural code (`includes/functions-*.php`) intermixed with modern object-oriented paradigms. These represent historical evolution and native logic that is preserved for functionality and compatibility rather than homogenized into abstract patterns.

## TIER 2: Architecture Topology Map
- **Core Engine**: The `includes/` directory contains the foundational logic.
- **API Interface**: `yourls-api.php` acts as the primary API endpoint.
- **Database Interaction**: Aura SQL is utilized for robust database queries.
- **Plugin System**: `user/plugins/` allows for extensive customization via hooks (`yourls_add_filter`, `yourls_do_action`).

## TIER 3: CI/CD Pipeline Cartograph
The project relies on PHPUnit for testing, configured via `phpunit.xml.dist`. Tests are located in the `tests/` directory. Dependencies are managed via Composer (`composer.json`). The pipeline mandates successful execution of all unit tests to maintain structural integrity.

## TIER 4: Dependency Matrix & Entropy Audit
- **PHP**: Core language constraint.
- **Aura SQL**: Essential for database abstraction.
- **Composer**: Dependency manager.
- **Entropy Assessment**: Legacy HTML generation functions (`includes/functions-html.php`) exhibit tight coupling, necessitating careful abstraction when integrating modern components. The `extract()` function has been removed from `yourls_html_head_output` to reduce entropy and variable overriding vulnerabilities.

## TIER 5: Operational Runbook & Cultural Artifacts Log
- **Prune-First Protocol**: Actively enforced to maintain repository hygiene, centralizing stray scripts into `scripts/`.
- **Testing Standard**: PHP 8 Attributes are used for PHPUnit annotations.
- **Vulnerability Mitigation**: The `yourls_maybe_unserialize` function now implements `allowed_classes` via filter to prevent object injection while preserving backward compatibility. JSONP endpoints enforce strict `Content-Type: application/javascript` headers prior to outputting data to mitigate XSS vectors.
