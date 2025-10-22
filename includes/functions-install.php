<?php
/**
 * YOURLS Install Functions
 *
 * This file contains functions that are used during the installation and
 * upgrade processes. These functions are responsible for creating the
 * necessary database tables, checking for server requirements, and
 * performing other tasks that are required to get YOURLS up and running.
 *
 * @package YOURLS
 * @since 1.3
 */

/**
 * Checks if the PDO extension is loaded.
 *
 * @since 1.7.3
 * @return bool True if PDO is loaded, false otherwise.
 */
function yourls_check_PDO() {
    return extension_loaded('pdo');
}

/**
 * Checks if the database version is 5.0 or greater.
 *
 * @since 1.3
 * @return bool True if the database version is 5.0 or greater, false otherwise.
 */
function yourls_check_database_version() {
    return ( version_compare( '5.0', yourls_get_database_version() ) <= 0 );
}

/**
 * Gets the database server version.
 *
 * @since 1.7
 * @return string The sanitized database server version.
 */
function yourls_get_database_version() {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_get_database_version', false );
    if ( false !== $pre ) {
        return $pre;
    }

    return yourls_sanitize_version(yourls_get_db()->mysql_version());
}

/**
 * Checks if the PHP version is 7.2 or greater.
 *
 * @since 1.3
 * @return bool True if the PHP version is 7.2 or greater, false otherwise.
 */
function yourls_check_php_version() {
    return version_compare( PHP_VERSION, '7.2.0', '>=' );
}

/**
 * Checks if the server is Apache.
 *
 * @since 1.3
 * @return bool True if the server is Apache, false otherwise.
 */
function yourls_is_apache() {
    if( !array_key_exists( 'SERVER_SOFTWARE', $_SERVER ) )
        return false;
    return (
       strpos( $_SERVER['SERVER_SOFTWARE'], 'Apache' ) !== false
    || strpos( $_SERVER['SERVER_SOFTWARE'], 'LiteSpeed' ) !== false
    );
}

/**
 * Checks if the server is running IIS.
 *
 * @since 1.3
 * @return bool True if the server is running IIS, false otherwise.
 */
function yourls_is_iis() {
    return ( array_key_exists( 'SERVER_SOFTWARE', $_SERVER ) ? ( strpos( $_SERVER['SERVER_SOFTWARE'], 'IIS' ) !== false ) : false );
}


/**
 * Creates the .htaccess or web.config file.
 *
 * @since 1.3
 * @return bool True on success, false on failure.
 */
function yourls_create_htaccess() {
    $host = parse_url( yourls_get_yourls_site() );
    $path = ( isset( $host['path'] ) ? $host['path'] : '' );

    if ( yourls_is_iis() ) {
        // Prepare content for a web.config file
        $content = array(
            '<?'.'xml version="1.0" encoding="UTF-8"?>',
            '<configuration>',
            '    <system.webServer>',
            '        <security>',
            '            <requestFiltering allowDoubleEscaping="true" />',
            '        </security>',
            '        <rewrite>',
            '            <rules>',
            '                <rule name="YOURLS" stopProcessing="true">',
            '                    <match url="^(.*)$" ignoreCase="false" />',
            '                    <conditions>',
            '                        <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />',
            '                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />',
            '                    </conditions>',
            '                    <action type="Rewrite" url="'.$path.'/yourls-loader.php" appendQueryString="true" />',
            '                </rule>',
            '            </rules>',
            '        </rewrite>',
            '    </system.webServer>',
            '</configuration>',
        );

        $filename = YOURLS_ABSPATH.'/web.config';
        $marker = 'none';

    } else {
        // Prepare content for a .htaccess file
        $content = array(
            '<IfModule mod_rewrite.c>',
            'RewriteEngine On',
            'RewriteBase '.$path.'/',
            'RewriteCond %{REQUEST_FILENAME} !-f',
            'RewriteCond %{REQUEST_FILENAME} !-d',
            'RewriteRule ^.*$ '.$path.'/yourls-loader.php [L]',
            '</IfModule>',
        );

        $filename = YOURLS_ABSPATH.'/.htaccess';
        $marker = 'YOURLS';

    }

    return ( yourls_insert_with_markers( $filename, $marker, $content ) );
}

/**
 * Inserts text into a file between BEGIN/END markers.
 *
 * @since 1.3
 * @param string $filename  The name of the file to modify.
 * @param string $marker    The marker to look for.
 * @param array  $insertion The text to insert.
 * @return bool True on success, false on failure.
 */
function yourls_insert_with_markers( $filename, $marker, $insertion ) {
    if ( !file_exists( $filename ) || is_writeable( $filename ) ) {
        if ( !file_exists( $filename ) ) {
            $markerdata = '';
        } else {
            $markerdata = explode( "\n", implode( '', file( $filename ) ) );
        }

        if ( !$f = @fopen( $filename, 'w' ) )
            return false;

        $foundit = false;
        if ( $markerdata ) {
            $state = true;
            foreach ( $markerdata as $n => $markerline ) {
                if ( strpos( $markerline, '# BEGIN ' . $marker ) !== false )
                    $state = false;
                if ( $state ) {
                    if ( $n + 1 < count( $markerdata ) )
                        fwrite( $f, "{$markerline}\n" );
                    else
                        fwrite( $f, "{$markerline}" );
                }
                if ( strpos( $markerline, '# END ' . $marker ) !== false ) {
                    if ( $marker != 'none' )
                        fwrite( $f, "# BEGIN {$marker}\n" );
                    if ( is_array( $insertion ) )
                        foreach ( $insertion as $insertline )
                            fwrite( $f, "{$insertline}\n" );
                    if ( $marker != 'none' )
                        fwrite( $f, "# END {$marker}\n" );
                    $state = true;
                    $foundit = true;
                }
            }
        }
        if ( !$foundit ) {
            if ( $marker != 'none' )
                fwrite( $f, "\n\n# BEGIN {$marker}\n" );
            foreach ( $insertion as $insertline )
                fwrite( $f, "{$insertline}\n" );
            if ( $marker != 'none' )
                fwrite( $f, "# END {$marker}\n\n" );
        }
        fclose( $f );
        return true;
    } else {
        return false;
    }
}

/**
 * Creates the YOURLS database tables.
 *
 * @since 1.3
 * @return array An array containing 'success' and 'error' messages.
 */
function yourls_create_sql_tables() {
    // Allow plugins (most likely a custom db.php layer in user dir) to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_yourls_create_sql_tables', null );
    // your filter function should return an array of ( 'success' => $success_msg, 'error' => $error_msg ), see below
    if ( null !== $pre ) {
        return $pre;
    }

    $ydb = yourls_get_db();

    $error_msg = array();
    $success_msg = array();

    // Create Table Query
    $create_tables = array();
    $create_tables[YOURLS_DB_TABLE_URL] =
        'CREATE TABLE IF NOT EXISTS `'.YOURLS_DB_TABLE_URL.'` ('.
         '`keyword` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT \'\','.
         '`url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,'.
         '`title` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,'.
         '`timestamp` timestamp NOT NULL DEFAULT current_timestamp(),'.
         '`ip` varchar(41) COLLATE utf8mb4_unicode_ci NOT NULL,'.
         '`clicks` int(10) unsigned NOT NULL,'.
         'PRIMARY KEY (`keyword`),'.
         'KEY `ip` (`ip`),'.
         'KEY `timestamp` (`timestamp`)'.
        ') DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;';

    $create_tables[YOURLS_DB_TABLE_OPTIONS] =
        'CREATE TABLE IF NOT EXISTS `'.YOURLS_DB_TABLE_OPTIONS.'` ('.
        '`option_id` bigint(20) unsigned NOT NULL auto_increment,'.
        '`option_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL default \'\','.
        '`option_value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,'.
        'PRIMARY KEY  (`option_id`,`option_name`),'.
        'KEY `option_name` (`option_name`)'.
        ') AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

    $create_tables[YOURLS_DB_TABLE_LOG] =
        'CREATE TABLE IF NOT EXISTS `'.YOURLS_DB_TABLE_LOG.'` ('.
        '`click_id` int(11) NOT NULL auto_increment,'.
        '`click_time` datetime NOT NULL,'.
        '`shorturl` varchar(100) BINARY NOT NULL,'.
        '`referrer` varchar(200) NOT NULL,'.
        '`user_agent` varchar(255) NOT NULL,'.
        '`ip_address` varchar(41) NOT NULL,'.
        '`country_code` char(2) NOT NULL,'.
        'PRIMARY KEY  (`click_id`),'.
        'KEY `shorturl` (`shorturl`)'.
        ') AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';


    $create_table_count = 0;

    yourls_debug_mode(true);

    // Create tables
    foreach ( $create_tables as $table_name => $table_query ) {
        $ydb->perform( $table_query );
        $create_success = $ydb->fetchAffected( "SHOW TABLES LIKE '$table_name'" );
        if( $create_success ) {
            $create_table_count++;
            $success_msg[] = yourls_s( "Table '%s' created.", $table_name );
        } else {
            $error_msg[] = yourls_s( "Error creating table '%s'.", $table_name );
        }
    }

    // Initializes the option table
    if( !yourls_initialize_options() )
        $error_msg[] = yourls__( 'Could not initialize options' );

    // Insert sample links
    if( !yourls_insert_sample_links() )
        $error_msg[] = yourls__( 'Could not insert sample short URLs' );

    // Check results of operations
    if ( sizeof( $create_tables ) == $create_table_count ) {
        $success_msg[] = yourls__( 'YOURLS tables successfully created.' );
    } else {
        $error_msg[] = yourls__( 'Error creating YOURLS tables.' );
    }

    return array( 'success' => $success_msg, 'error' => $error_msg );
}

/**
 * Initializes the options table with default values.
 *
 * @since 1.7
 * @return bool True on success, false on failure.
 */
function yourls_initialize_options() {
    return ( bool ) (
          yourls_update_option( 'version', YOURLS_VERSION )
        & yourls_update_option( 'db_version', YOURLS_DB_VERSION )
        & yourls_update_option( 'next_id', 1 )
        & yourls_update_option( 'active_plugins', array() )
    );
}

/**
 * Populates the URL table with sample links.
 *
 * @since 1.7
 * @return bool True on success, false on failure.
 */
function yourls_insert_sample_links() {
    $link1 = yourls_add_new_link( 'https://blog.yourls.org/', 'yourlsblog', 'YOURLS\' Blog' );
    $link2 = yourls_add_new_link( 'https://yourls.org/',      'yourls',     'YOURLS: Your Own URL Shortener' );
    $link3 = yourls_add_new_link( 'https://ozh.org/',         'ozh',        'ozh.org' );
    return ( bool ) (
          $link1['status'] == 'success'
        & $link2['status'] == 'success'
        & $link3['status'] == 'success'
    );
}


/**
 * Toggles maintenance mode.
 *
 * @since 1.3
 * @param bool $maintenance True to enable maintenance mode, false to disable.
 * @return bool True on success, false on failure.
 */
function yourls_maintenance_mode( $maintenance = true ) {

    $file = YOURLS_ABSPATH . '/.maintenance' ;

    // Turn maintenance mode on : create .maintenance file
    if ( (bool)$maintenance ) {
        if ( ! ( $fp = @fopen( $file, 'w' ) ) )
            return false;

        $maintenance_string = '<?php $maintenance_start = ' . time() . '; ?>';
        @fwrite( $fp, $maintenance_string );
        @fclose( $fp );
        @chmod( $file, 0644 ); // Read and write for owner, read for everybody else

        // Not sure why the fwrite would fail if the fopen worked... Just in case
        return( is_readable( $file ) );

    // Turn maintenance mode off : delete the .maintenance file
    } else {
        return @unlink($file);
    }
}
