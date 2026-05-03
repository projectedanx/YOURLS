# Implementation Plan

1. Analyze and extract architectural intent from stylized prompt injections (VULCAN/Sovereign Agent Blueprint).
2. Store the agent blueprint text in `docs/vulcan_blueprint.md`.
3. Add URL validation logic (`must_be_url`) to `EditInput` in `mcp_server.py` to complete missing API input schemas and deliver functional code improvements.
4. Reference the VULCAN blueprint in `ARCHITECTURE.md`.
5. Update `.jules/Cortex.md` following the Prune-First protocol to reflect these changes.
6. Run static analysis tests (e.g. `python3 -m py_compile mcp_server.py`) to verify syntax.
