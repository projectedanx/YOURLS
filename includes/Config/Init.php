<?php

/**
 * YOURLS actions upon instantiating
 */

namespace YOURLS\Config;

class Init {

    /**
     * @var InitDefaults
     */
    protected $actions;

    /**
     * @since  1.7.3
     *
     * @param InitDefaults $actions
     */
    public function __construct(InitDefaults $actions) {

        $this->actions = $actions;

        $this->setup_environment();

        // Abort initialization here if fast init wanted (for tests/debug/do not use)
        if ($this->actions->return_if_fast_init === true && defined('YOURLS_FAST_INIT') && YOURLS_FAST_INIT){
            return;
        }

        $this->setup_core();
        $this->setup_plugins();
    }

    /**
     * Set up the initial environment.
     *
     * @return void
     */
    private function setup_environment() {
        // Include core files
        if ($this->actions->include_core_funcs === true) {
            $this->include_core_functions();
        }

        // Enforce UTC timezone. Date/time can be adjusted with a plugin.
        if ($this->actions->default_timezone === true) {
            date_default_timezone_set( 'UTC' );
        }

        // Check if we are in maintenance mode - if yes, it will die here.
        if ($this->actions->check_maintenance_mode === true) {
            yourls_check_maintenance_mode();
        }

        // Compatibility: REQUEST_URI for IIS
        if ($this->actions->fix_request_uri === true) {
            yourls_fix_request_uri();
        }

        // If request for an admin page is http:// and SSL is required, redirect
        if ($this->actions->redirect_ssl === true) {
            $this->redirect_ssl_if_needed();
        }

        // Create the YOURLS object $ydb that will contain everything we globally need
        if ($this->actions->include_db === true) {
            $this->include_db_files();
        }

        // Allow early and unconditional inclusion of custom code
        if ($this->actions->include_cache === true) {
            $this->include_cache_files();
        }
    }

    /**
     * Load core options and check for install/upgrade.
     *
     * @return void
     */
    private function setup_core() {
        // Read options right from start
        if ($this->actions->get_all_options === true) {
            yourls_get_all_options();
        }

        // Register shutdown function
        if ($this->actions->register_shutdown === true) {
            register_shutdown_function( 'yourls_shutdown' );
        }

        // Core now loaded
        if ($this->actions->core_loaded === true) {
            yourls_do_action( 'init' ); // plugins can't see this, not loaded yet
        }

        // Check if need to redirect to install procedure
        if ($this->actions->redirect_to_install === true) {
            if (!yourls_is_installed() && !yourls_is_installing()) {
                yourls_no_cache_headers();
                yourls_redirect( yourls_admin_url('install.php'), 307 );
                exit();
            }
        }

        // Check if upgrade is needed (bypassed if upgrading or installing)
        if ($this->actions->check_if_upgrade_needed === true) {
            if (!yourls_is_upgrading() && !yourls_is_installing() && yourls_upgrade_is_needed()) {
                yourls_no_cache_headers();
                yourls_redirect( yourls_admin_url('upgrade.php'), 307 );
                exit();
            }
        }
    }

    /**
     * Load plugins, locales, and admin updates.
     *
     * @return void
     */
    private function setup_plugins() {
        // Load all plugins
        if ($this->actions->load_plugins === true) {
            yourls_load_plugins();
        }

        // Trigger plugin loaded action
        if ($this->actions->plugins_loaded_action === true) {
            yourls_do_action( 'plugins_loaded' );
        }

        // Load locale
        if ($this->actions->load_default_textdomain === true) {
            yourls_load_default_textdomain();
        }

        // Is there a new version of YOURLS ?
        if ($this->actions->check_new_version === true) {
            if (yourls_is_installed() && !yourls_is_upgrading()) {
                yourls_tell_if_new_version();
            }
        }

        if ($this->actions->init_admin === true) {
            if (yourls_is_admin()) {
                yourls_do_action( 'admin_init' );
            }
        }
    }

    /**
     * @since  1.7.3
     * @return void
     */
    public function redirect_ssl_if_needed() {
        if (yourls_is_admin() && yourls_needs_ssl() && !yourls_is_ssl()) {
            if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {
                yourls_redirect( preg_replace( '|^http://|', 'https://', $_SERVER['REQUEST_URI'] ) );
            } else {
                yourls_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
            }
            exit();
        }
    }

    /**
     * @since  1.7.3
     * @return void
     */
    public function include_db_files() {
        // Attempt to open drop-in replacement for the DB engine else default to core engine
        $file = YOURLS_USERDIR . '/db.php';
        $attempt = false;
        if(file_exists($file)) {
            $attempt = yourls_include_file_sandbox( $file );
            // Check if we have an error to display
            if ( is_string( $attempt ) ) {
                yourls_add_notice( $attempt );
            }
        }

        // Fallback to core DB engine
        if ( $attempt !== true ) {
            require_once YOURLS_INC . '/class-mysql.php';
            yourls_db_connect();
        }
    }

    /**
     * Include custom extension file.
     *
     * "Cache" stands for "Custom Additional Code for Hazardous Extensions".
     *
     * @since  1.7.3
     * @return void
     */
    public function include_cache_files() {
        $file = YOURLS_USERDIR . '/cache.php';
        $attempt = false;
        if(file_exists($file)) {
            $attempt = yourls_include_file_sandbox($file);
            // Check if we have an error to display
            if (is_string($attempt)) {
                yourls_add_notice($attempt);
            }
        }
    }

    /**
     * @since  1.7.3
     * @return void
     */
    public function include_core_functions() {
        require_once YOURLS_INC.'/version.php';
        require_once YOURLS_INC.'/functions.php';
        require_once YOURLS_INC.'/functions-geo.php';
        require_once YOURLS_INC.'/functions-shorturls.php';
        require_once YOURLS_INC.'/functions-debug.php';
        require_once YOURLS_INC.'/functions-options.php';
        require_once YOURLS_INC.'/functions-links.php';
        require_once YOURLS_INC.'/functions-plugins.php';
        require_once YOURLS_INC.'/functions-formatting.php';
        require_once YOURLS_INC.'/functions-api.php';
        require_once YOURLS_INC.'/functions-kses.php';
        require_once YOURLS_INC.'/functions-l10n.php';
        require_once YOURLS_INC.'/functions-compat.php';
        require_once YOURLS_INC.'/functions-html.php';
        require_once YOURLS_INC.'/functions-http.php';
        require_once YOURLS_INC.'/functions-infos.php';
        require_once YOURLS_INC.'/functions-deprecated.php';
        require_once YOURLS_INC.'/functions-auth.php';
        require_once YOURLS_INC.'/functions-upgrade.php';
        require_once YOURLS_INC.'/functions-install.php';
    }

}
