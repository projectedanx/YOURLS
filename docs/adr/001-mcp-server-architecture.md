# 001 - MCP Server Integration and Isolation Strategy

## Context
The YOURLS system exposes a legacy HTTP API (`yourls-api.php`). To support AI integrations, an MCP (Model Context Protocol) server has been implemented via `mcp_server.py`. The challenge lies in ensuring this new integration point maintains strict domain isolation from the PHP core, prevents state corruption (Zero-Trust), and avoids coupling the AI orchestrator directly to the underlying database (Mereological Mandate).

## Decision
We will establish a strict **API-Led Integration Strategy** for the MCP Server.
- The `mcp_server.py` component will act *exclusively* as a client to `yourls-api.php`.
- It is forbidden from importing database connectors, reading configuration files (like `user/config.php`), or interacting directly with the MySQL/MariaDB cluster.
- Identity and credentials will be injected via environment variables (`YOURLS_USERNAME`, `YOURLS_PASSWORD`, `YOURLS_SIGNATURE`) at runtime, conforming to a Zero-Trust boundary.
- Error handling in `mcp_server.py` will implement SERF-compliant formatting to prevent LLM hallucination and ensure deterministic failure states.

## Status
Accepted

## Consequences
**Positive:**
- **Zero-Trust Boundaries:** The PHP monolith's security model remains intact. The MCP server has no more access than a standard authenticated user.
- **Transitivity Avoidance:** We satisfy the Mereological Mandate (Rule 1). The AI orchestrator (Whole) does not inherit direct state access to the database (Part).
- **Decoupling:** If the underlying database schema changes, the MCP server is unaffected as long as the API contract holds.

**Negative:**
- **Double Serialization Tax:** Data must be serialized to JSON by PHP, sent over HTTP to Python, deserialized, processed, and then reserialized for the MCP protocol. This increases latency compared to direct database access.
- **API Bottleneck:** The MCP server is limited entirely by the features exposed in `yourls-api.php`. It cannot perform arbitrary aggregations or graph queries without upstream modifications.

## Mitigations
- The double serialization tax is acceptable because AI orchestration workflows are inherently high-latency due to LLM generation times. The overhead of local HTTP transport is negligible in this context.
- If complex aggregations are required, we will extend `yourls-api.php` with new dedicated endpoints rather than allowing the MCP server to bypass the API boundary.
