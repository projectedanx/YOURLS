<?php

/**
 * Define the YOURLS config
 */

namespace YOURLS\Config;

use YOURLS\Exceptions\ConfigException;

class Config {

    /**
     * @var string
     */
    protected $root;

    /**
     * @var string
     */
    protected $config;

    /**
     * @since  1.7.3
     * @param  string $config   Optional user defined config path
     */
    public function __construct($config = '') {
        $this->set_root( $this->fix_win32_path( dirname( dirname( __DIR__ ) ) ) );
        $this->set_config($config);
    }

    /**
     * Convert antislashes to slashes
     *
     * @since  1.7.3
     * @param  string  $path
     * @return string  path with \ converted to /
     */
    public function fix_win32_path($path) {
        return str_replace('\\', '/', $path);
    }

    /**
     * @since  1.7.3
     * @param  string $config   path to config file
     * @return void
     */
    public function set_config($config) {
        $this->config = $config;
    }

    /**
     * @since  1.7.3
     * @param  string $root   path to YOURLS root directory
     * @return void
     */
    public function set_root($root) {
        $this->root = $root;
    }

    /**
     * Find config.php, either user defined or from standard location
     *
     * @since  1.7.3
     * @return string         path to found config file
     * @throws ConfigException
     */
    public function find_config() {

        $config = $this->fix_win32_path($this->config);

        if (!empty($config) && is_readable($config)) {
            return $config;
        }

        if (!empty($config) && !is_readable($config)) {
            throw new ConfigException("User defined config not found at '$config'");
        }

        // config.php in /user/
        if (file_exists($this->root . '/user/config.php')) {
            return $this->root . '/user/config.php';
        }

        // config.php in /includes/
        if (file_exists($this->root . '/includes/config.php')) {
            return $this->root . '/includes/config.php';
        }

        // config.php not found :(

        throw new ConfigException('Cannot find config.php. Please read the readme.html to learn how to install YOURLS');
    }

    /**
     * Define core constants that have not been user defined in config.php
     *
     * @since  1.7.3
     * @return void
     * @throws ConfigException
     */
    public function define_core_constants() {
        // Check minimal config job has been properly done
        $must_haves = array('YOURLS_DB_USER', 'YOURLS_DB_PASS', 'YOURLS_DB_NAME', 'YOURLS_DB_HOST', 'YOURLS_DB_PREFIX', 'YOURLS_SITE');
        foreach($must_haves as $must_have) {
            if (!defined($must_have)) {
                throw new ConfigException('Config is incomplete (missing at least '.$must_have.') Check config-sample.php and edit your config accordingly');
            }
        }

        $defaults = array(
            'YOURLS_ABSPATH'             => $this->root,
            'YOURLS_INC'                 => function() { return YOURLS_ABSPATH . '/includes'; },
            'YOURLS_USERDIR'             => function() { return YOURLS_ABSPATH . '/user'; },
            'YOURLS_USERURL'             => function() { return trim( YOURLS_SITE, '/' ) . '/user'; },
            'YOURLS_ASSETDIR'            => function() { return YOURLS_ABSPATH . '/assets'; },
            'YOURLS_ASSETURL'            => function() { return trim( YOURLS_SITE, '/' ) . '/assets'; },
            'YOURLS_LANG_DIR'            => function() { return YOURLS_USERDIR . '/languages'; },
            'YOURLS_PLUGINDIR'           => function() { return YOURLS_USERDIR . '/plugins'; },
            'YOURLS_PLUGINURL'           => function() { return YOURLS_USERURL . '/plugins'; },
            'YOURLS_THEMEDIR'            => function() { return YOURLS_USERDIR . '/themes'; },
            'YOURLS_THEMEURL'            => function() { return YOURLS_USERURL . '/themes'; },
            'YOURLS_PAGEDIR'             => function() { return YOURLS_USERDIR . '/pages'; },
            'YOURLS_DB_TABLE_URL'        => function() { return YOURLS_DB_PREFIX . 'url'; },
            'YOURLS_DB_TABLE_OPTIONS'    => function() { return YOURLS_DB_PREFIX . 'options'; },
            'YOURLS_DB_TABLE_LOG'        => function() { return YOURLS_DB_PREFIX . 'log'; },
            'YOURLS_FLOOD_DELAY_SECONDS' => 15,
            'YOURLS_FLOOD_IP_WHITELIST'  => '',
            'YOURLS_COOKIE_LIFE'         => 60 * 60 * 24 * 7,
            'YOURLS_NONCE_LIFE'          => 43200,
            'YOURLS_NOSTATS'             => false,
            'YOURLS_ADMIN_SSL'           => false,
            'YOURLS_DEBUG'               => false,
        );

        foreach ( $defaults as $name => $value ) {
            if ( !defined( $name ) ) {
                define( $name, is_callable( $value ) ? $value() : $value );
            }
        }

        // Error reporting
        if (defined( 'YOURLS_DEBUG' ) && YOURLS_DEBUG == true ) {
            error_reporting( -1 );
        } else {
            error_reporting( E_ERROR | E_PARSE );
        }
    }

}
