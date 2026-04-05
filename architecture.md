Root: PHP
├── Auth: Native — Session/Cookie
├── DB: MySQL/MariaDB — PDO
├── API: Native — REST/JSON
├── UI: Native — HTML/CSS/JS (no framework)
└── Infra: Local/Docker — PHP-FPM/Nginx or Apache

DATA FLOWS:
User -> [Auth] -> [API] -> [DB] -> [API] -> [UI]

MEREOLOGICAL MAP:
[Component] ∈ [Service] ∈ [Module] ∈ [Root]

STACK_DECLARATION: PHP + Native Auth + MySQL + Native REST + Native UI
EPISTEMIC_WORLD: YOURLS
MEREOLOGICAL_BOUNDARIES: Component <- Service <- Module <- Root
DIRICHLET_MONITOR: active (halt if edge_value > 0.85)
