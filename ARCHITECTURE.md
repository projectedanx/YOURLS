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

## Core Initialization & Plugin Hook Architecture

```mermaid
sequenceDiagram
    participant Index as Entry Point (e.g. index.php)
    participant Config as YOURLS\Config\Init
    participant DB as Database Engine
    participant Plugins as Plugin System

    Index->>Config: require 'load-yourls.php'
    Config->>Config: Define Core Constants & Include Functions
    Config->>DB: include_db_files() (Sandbox or class-mysql.php)
    Config->>Config: Get all options from DB
    Config->>Plugins: yourls_do_action('init')
    Config->>Plugins: yourls_load_plugins()
    loop Active Plugins
        Plugins->>Plugins: include_file_sandbox()
        Plugins->>Plugins: Hook functions via yourls_add_action() / yourls_add_filter()
    end
    Config->>Plugins: yourls_do_action('plugins_loaded')
    Config->>Config: Load Text Domain (L10n)
    alt Is Admin Request
        Config->>Plugins: yourls_do_action('admin_init')
    end
    Index->>Plugins: Execution continues, invoking hooks (yourls_do_action, yourls_apply_filter)
```

## Authentication Lifecycle (functions-auth.php)

```mermaid
sequenceDiagram
    participant User
    participant Auth as YOURLS Auth (yourls_is_valid_user)
    participant Config as YOURLS Config
    participant Hooks as Plugin Hooks

    User->>Auth: Request Admin Area (with Cookie or Credentials)
    Auth->>Hooks: apply_filters('shunt_is_valid_user')
    alt Shunt returns true
        Hooks-->>Auth: bypass standard auth
    else Proceed with standard auth
        alt Action is 'logout'
            Auth->>Auth: Verify Logout Nonce
            Auth->>Auth: Destroy Cookie
            Auth-->>User: 302 Redirect to Login Page
        else Action is not logout
            Auth->>Auth: Check Auth Cookie (yourls_check_auth_cookie)
            alt Cookie is valid
                Auth-->>User: Granted Access
            else Cookie is invalid or missing
                alt HTTP Credentials provided
                    Auth->>Config: Check Basic/Digest Auth
                else POST Credentials provided
                    Auth->>Config: Check Username/Password (yourls_check_username_password)
                end

                alt Credentials match
                    Auth->>Auth: Generate Cookie & Hash Password (if needed)
                    Auth-->>User: Granted Access
                else Credentials do not match
                    Auth->>Hooks: do_action('login_failed')
                    Auth-->>User: 403 / Redirect to Login Page
                end
            end
        end
    end
```

## Loader Routing State Machine (yourls-loader.php)

```mermaid
stateDiagram-v2
    [*] --> Request_Received: $_SERVER['REQUEST_URI']

    Request_Received --> Favicon: /favicon.ico
    Favicon --> [*]: Return 1x1 GIF & Exit

    Request_Received --> Robots_Txt: /robots.txt
    Robots_Txt --> [*]: Return text/plain & Exit

    Request_Received --> Parse_Request: Any other URI
    Parse_Request --> Pre_Load_Template: Extract keyword and stats modifiers
    Pre_Load_Template --> Scheme_Check

    state Scheme_Check {
        [*] --> Has_Protocol
        Has_Protocol --> Bookmarklet_Redirect: e.g. http://..., https://...
        Bookmarklet_Redirect --> Redirect_Admin: 302 Redirect to /admin/index.php

        [*] --> No_Protocol
        No_Protocol --> Keyword_Check: e.g. abc, abc+, abc+all
    }

    Keyword_Check --> Valid_Keyword: Exists in DB or Page
    Valid_Keyword --> Route_To_Go: Keyword only
    Route_To_Go --> [*]: include yourls-go.php & Exit

    Valid_Keyword --> Route_To_Infos: Keyword + Stats (+)
    Route_To_Infos --> [*]: include yourls-infos.php & Exit

    Keyword_Check --> Invalid_Keyword: Not Found
    Invalid_Keyword --> Fallback_Redirect: 302 Redirect to YOURLS_SITE
    Fallback_Redirect --> [*]: Exit
```
## URL Shortening Core Flow (yourls_add_new_link)

```mermaid
sequenceDiagram
    participant Caller
    participant Core as Core (yourls_add_new_link)
    participant DB as Database
    participant Hooks as Plugin Hooks

    Caller->>Core: yourls_add_new_link($url, $keyword, $title)
    Core->>Hooks: apply_filters('shunt_add_new_link')
    alt Shunt returns data
        Hooks-->>Core: Short-circuit Result
        Core-->>Caller: Return Result
    else Proceed with core logic
        Core->>Core: Sanitize & Validate URL
        Core->>Core: Check IP Flood & Redirection Loops
        Core->>DB: Check if Long URL exists (if duplicates not allowed)
        alt URL already stored
            DB-->>Core: Existing Short URL
            Core-->>Caller: 400 Bad Request (Duplicate)
        else URL is new
            Core->>Core: Sanitize or Fetch Title
            alt Custom Keyword provided
                Core->>Core: Sanitize Keyword
                Core->>DB: Check if Keyword is free
                alt Keyword taken
                    Core-->>Caller: 400 Bad Request (Keyword exists)
                end
            else No Keyword provided
                loop Until Keyword is free
                    Core->>DB: Get Next Decimal
                    Core->>Core: Convert Decimal to String Keyword
                    Core->>DB: Check if Keyword is free
                end
                Core->>DB: Update Next Decimal
            end

            Core->>DB: yourls_insert_link_in_db()
            alt Insert successful
                DB-->>Core: Success
                Core->>Hooks: do_action('post_add_new_link')
                Core-->>Caller: 200 OK (Short URL Data)
            else Concurrency Exception
                DB-->>Core: DB Exception
                Core-->>Caller: 503 Service Unavailable
            else General DB Error
                DB-->>Core: False
                Core-->>Caller: 500 Internal Server Error
            end
        end
    end
```

## Core Version Check Pipeline (yourls_check_core_version)

```mermaid
sequenceDiagram
    participant Admin
    participant Core as Core (yourls_check_core_version)
    participant DB as Database (Options)
    participant API as External API (api.yourls.org)

    Admin->>Core: Trigger Admin Request
    Core->>DB: yourls_get_option('core_version_checks')
    DB-->>Core: Check Data (failed_attempts, last_attempt, etc.)

    alt Valid Cached Data & Recent Check
        Core-->>Admin: Return Cached Version Result
    else Expired or Missing Check Data
        Core->>DB: yourls_get_db_stats() (URLs, Clicks)
        DB-->>Core: Stats
        Core->>Core: Gather Server & Usage Data ($stuff)
        Core->>API: HTTP POST /core/version/1.1/

        alt API Request Fails or Invalid JSON
            API-->>Core: HTTP Error or Invalid Data
            Core->>Core: Increment failed_attempts
            Core->>DB: yourls_update_option('core_version_checks', Update)
            Core-->>Admin: Return Error/Retry Later
        else API Request Succeeds
            API-->>Core: JSON response (latest_version, zipurl)
            Core->>Core: Reset failed_attempts, update last_result
            Core->>DB: yourls_update_option('core_version_checks', Update)
            Core-->>Admin: Return Latest Version Info
        end
    end
```

## System Installation Pipeline (admin/install.php)

```mermaid
sequenceDiagram
    participant Admin
    participant Install as Install Script (admin/install.php)
    participant Core as Core Checks
    participant FS as File System
    participant DB as Database Engine

    Admin->>Install: GET /admin/install.php
    Install->>Core: yourls_check_PDO()
    Install->>Core: yourls_check_database_version()
    Install->>Core: yourls_check_php_version()
    Install->>Core: yourls_is_installed()
    alt Already Installed
        Install->>FS: check & yourls_create_htaccess()
        Install-->>Admin: Show "Already installed" error
    else Not Installed & Pre-requisites Met
        Admin->>Install: POST /admin/install.php?install=Install YOURLS
        Install->>FS: yourls_create_htaccess()
        alt IIS Server
            FS-->>Install: Create web.config
        else Apache/Other
            FS-->>Install: Create .htaccess
        end
        Install->>DB: yourls_create_sql_tables()
        DB->>DB: Create URL, OPTIONS, LOG tables
        DB->>DB: yourls_initialize_options() (version, next_id, etc.)
        DB->>DB: yourls_insert_sample_links()
        DB-->>Install: Return Success/Error Arrays
        Install-->>Admin: Show Success & Admin Link
    end
```
