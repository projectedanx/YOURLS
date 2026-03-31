# YOURLS Architecture Map

This document visualizes the hidden boundaries, data flows, and architectural boundaries of the YOURLS project using Mermaid.js diagrams.

## System Context (C4 Model)

```mermaid
C4Context
  title System Context diagram for YOURLS
  Person(user, "User / Admin", "A user shortening a URL or an admin managing the system.")
  Person(visitor, "Visitor", "A user clicking on a short URL.")
  System(yourls, "YOURLS", "Your Own URL Shortener system.")
  SystemDb(db, "Database", "MySQL/MariaDB database storing URLs, options, and logs.")
  System_Ext(plugins, "Plugins", "Extensions modifying YOURLS behavior.")

  Rel(user, yourls, "Uses / Manages")
  Rel(visitor, yourls, "Visits short URLs")
  Rel(yourls, db, "Reads from and writes to")
  Rel(yourls, plugins, "Extends functionality via hooks")
```

## Relational Schema Topography (ER Diagram)

```mermaid
erDiagram
    URL {
        varchar(100) keyword PK "CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT ''"
        text url "CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL"
        text title "COLLATE utf8mb4_unicode_ci DEFAULT NULL"
        timestamp timestamp "NOT NULL DEFAULT current_timestamp()"
        varchar(41) ip "COLLATE utf8mb4_unicode_ci NOT NULL"
        int(10)_unsigned clicks "NOT NULL"
    }

    OPTIONS {
        bigint(20)_unsigned option_id PK "NOT NULL auto_increment"
        varchar(64) option_name PK "COLLATE utf8mb4_unicode_ci NOT NULL default ''"
        longtext option_value "COLLATE utf8mb4_unicode_ci NOT NULL"
    }

    LOG {
        int(11) click_id PK "NOT NULL auto_increment"
        datetime click_time "NOT NULL"
        varchar(100) shorturl "BINARY NOT NULL"
        varchar(200) referrer "NOT NULL"
        varchar(255) user_agent "NOT NULL"
        varchar(41) ip_address "NOT NULL"
        char(2) country_code "NOT NULL"
    }

    URL ||--o{ LOG : "logs clicks"
```

## Short URL Redirection Pipeline (yourls-go.php)

```mermaid
sequenceDiagram
    participant Visitor
    participant Core as YOURLS Core (yourls-go.php)
    participant DB as Database
    participant Hooks as Plugin Hooks

    Visitor->>Core: GET /{keyword}
    Core->>Core: Load Context & Config
    alt Keyword missing
        Core->>Hooks: do_action('redirect_no_keyword')
        Core-->>Visitor: 301 Redirect to YOURLS_SITE
    else Keyword present
        Core->>Core: Sanitize Keyword
        alt Keyword is an internal page
            Core-->>Visitor: Render Page & Exit
        else Keyword is a short URL
            Core->>DB: Query Long URL
            alt URL Found
                DB-->>Core: Return Long URL
                Core->>Hooks: do_action('pre_redirect', ...)
                Core-->>Visitor: 301 Redirect to Long URL
            else URL Not Found
                DB-->>Core: Null
                Core->>Hooks: do_action('redirect_keyword_not_found')
                Core-->>Visitor: 302 Redirect to YOURLS_SITE
            end
        end
    end
```

## API Request Pipeline (yourls-api.php)

```mermaid
sequenceDiagram
    participant Client
    participant API as API Core (yourls-api.php)
    participant Auth as Authentication
    participant Hooks as Plugin Hooks

    Client->>API: GET/POST /yourls-api.php?action={action}&format={format}
    API->>API: Load Context & Config
    API->>Auth: yourls_maybe_require_auth()
    alt Authentication Failed
        Auth-->>API: Halt Execution
        API-->>Client: 403 Forbidden / Error Response
    else Authentication Successful
        Auth-->>API: Authorized
        API->>Hooks: do_action('api', $action)
        API->>Hooks: apply_filters('api_actions', default_actions)
        Hooks-->>API: Registered Actions List
        API->>API: Dispatch Registered Callback
        API->>Hooks: apply_filters('api_action_' . $action)
        alt Valid Action
            Hooks-->>API: $return array (Action Result)
        else Unknown Action
            Hooks-->>API: false
            API->>API: Build 400 Error $return array
        end
        API->>API: yourls_api_output($format, $return)
        API-->>Client: Formatted Response (XML, JSON, JSONP, Simple)
    end
```
