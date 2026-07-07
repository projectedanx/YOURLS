# 0xCARTO Blueprint: Repository Overview

## TIER 1: Repository Identity & Ontological Glossary
This is the core YOURLS repository. A PHP-based URL shortener.
Glossary:
- `includes/`: Core functional components
- `tests/`: PHPUnit testing suite
- `scripts/`: Centralized utility scripts
- `composer.json`: PHP dependency manifest

## TIER 2: Architecture Topology Map
- **Frontend**: Basic PHP/HTML templates
- **API**: `yourls-api.php`
- **Storage**: MySQL via Aura/SQL
- **Plugins**: Hook-driven architecture

## TIER 3: CI/CD Pipeline Cartograph
- Relies on basic PHPUnit execution (`phpunit`) locally.
- Vendor generation via Composer scripts (`post-update-cmd`).

## TIER 4: Dependency Matrix & Entropy Audit
- Strict pinned dependencies in `composer.json`
- `aura/sql` pinned to `^5.0` to support PHP 8.3 environments.
- Extension dependencies securely pinned to explicit PHP versions or library constraints.

## TIER 5: Operational Runbook & Cultural Artifacts Log
- **Root Hygiene**: Strictly enforced. Non-standard utility scripts (`test_composer.php`, `run_test.php`, etc) are routed to `scripts/`.
- **Golden Scars**: `config.platform.php` override was removed as it forced `^8.4` dependency structures incompatible with local test matrices.
